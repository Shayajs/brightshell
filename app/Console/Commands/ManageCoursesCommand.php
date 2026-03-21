<?php

namespace App\Console\Commands;

use App\Models\StudentCourse;
use App\Models\StudentCourseQuiz;
use App\Models\User;
use App\Services\StudentCourses\QuizJsonImporter;
use App\Services\StudentCourses\ScheduleOverlapChecker;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class ManageCoursesCommand extends Command
{
    protected $signature = 'courses
                            {action : list|add|import-quiz}
                            {email? : E-mail de l’élève (rôle student)}
                            {--title= : Titre du cours (action add)}
                            {--description=}
                            {--status=planned}
                            {--starts-at= : Date début YYYY-MM-DD}
                            {--ends-at= : Date fin YYYY-MM-DD}
                            {--weekday= : 1=lundi … 7=dimanche (créneau récurrent)}
                            {--time-start= : HH:MM}
                            {--time-end= : HH:MM}
                            {--notes=}
                            {--course= : Fragment du titre du cours cible (import-quiz)}
                            {--file= : Chemin absolu ou relatif au JSON (import-quiz)}
                            {--quiz-title= : Titre du quiz créé (import-quiz, défaut : Quiz importé)}';

    protected $description = 'Gestion des cours élèves par e-mail : lister, ajouter un cours (avec créneau), importer un quiz JSON (idéal pour sortie IA).';

    public function handle(ScheduleOverlapChecker $overlapChecker, QuizJsonImporter $importer): int
    {
        $action = (string) $this->argument('action');
        $email = $this->argument('email');

        if (! in_array($action, ['list', 'add', 'import-quiz'], true)) {
            $this->error('Action inconnue. Utilise : list, add, import-quiz');

            return self::FAILURE;
        }

        if ($email === null || $email === '') {
            $this->error('Indique l’e-mail de l’élève.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();
        if ($user === null || ! $user->hasRole('student')) {
            $this->error('Aucun utilisateur avec le rôle « élève » pour cet e-mail.');

            return self::FAILURE;
        }

        return match ($action) {
            'list' => $this->runList($user),
            'add' => $this->runAdd($user, $overlapChecker),
            'import-quiz' => $this->runImportQuiz($user, $importer),
        };
    }

    private function runList(User $user): int
    {
        $courses = $user->studentCourses()->withCount('quizzes')->orderBy('sort_order')->orderBy('id')->get();

        if ($courses->isEmpty()) {
            $this->info('Aucun cours pour '.$user->email);

            return self::SUCCESS;
        }

        $rows = $courses->map(fn (StudentCourse $c) => [
            $c->id,
            $c->title,
            $c->status,
            $c->hasWeeklySchedule()
                ? ($c->scheduleWeekdayLabel().' '.$c->scheduleTimeStartShort().'–'.$c->scheduleTimeEndShort())
                : '—',
            $c->quizzes_count,
        ]);

        $this->table(['ID', 'Titre', 'Statut', 'Créneau', 'Quiz'], $rows->all());

        return self::SUCCESS;
    }

    private function runAdd(User $user, ScheduleOverlapChecker $overlapChecker): int
    {
        $title = (string) $this->option('title');
        if ($title === '') {
            $this->error('Utilise --title="Nom du cours"');

            return self::FAILURE;
        }

        $status = (string) $this->option('status');
        if (! array_key_exists($status, StudentCourse::statuses())) {
            $this->error('Statut invalide. Valeurs : '.implode(', ', array_keys(StudentCourse::statuses())));

            return self::FAILURE;
        }

        $data = [
            'title' => $title,
            'description' => $this->option('description') ?: null,
            'status' => $status,
            'starts_at' => $this->option('starts-at') ?: null,
            'ends_at' => $this->option('ends-at') ?: null,
            'notes' => $this->option('notes') ?: null,
            'schedule_weekday' => $this->option('weekday') !== null && $this->option('weekday') !== ''
                ? (int) $this->option('weekday')
                : null,
            'schedule_time_start' => $this->option('time-start') ?: null,
            'schedule_time_end' => $this->option('time-end') ?: null,
        ];

        $max = (int) ($user->studentCourses()->max('sort_order') ?? 0);
        $data['user_id'] = $user->id;
        $data['sort_order'] = $max + 1;

        try {
            $this->validateSchedulePayload($data);
            $conflicts = $overlapChecker->conflictingCourses($user->id, [
                'schedule_weekday' => $data['schedule_weekday'],
                'schedule_time_start' => $data['schedule_time_start'],
                'schedule_time_end' => $data['schedule_time_end'],
            ], null);
            if ($conflicts->isNotEmpty()) {
                $this->error('Créneau en conflit avec : '.$conflicts->pluck('title')->implode(', '));

                return self::FAILURE;
            }
        } catch (ValidationException $e) {
            foreach ($e->errors() as $msgs) {
                foreach ($msgs as $m) {
                    $this->error($m);
                }
            }

            return self::FAILURE;
        }

        $course = StudentCourse::query()->create($data);
        $this->info('Cours #'.$course->id.' créé pour '.$user->email);

        return self::SUCCESS;
    }

    private function runImportQuiz(User $user, QuizJsonImporter $importer): int
    {
        $needle = (string) $this->option('course');
        $path = (string) $this->option('file');
        if ($needle === '' || $path === '') {
            $this->error('Utilise --course="extrait titre" et --file=/chemin/quiz.json');

            return self::FAILURE;
        }

        $fullPath = $path;
        if (! is_file($fullPath)) {
            $fullPath = base_path($path);
        }
        if (! is_readable($fullPath)) {
            $this->error('Fichier introuvable ou illisible : '.$path);

            return self::FAILURE;
        }

        $json = file_get_contents($fullPath);
        if ($json === false) {
            $this->error('Lecture du fichier impossible.');

            return self::FAILURE;
        }

        $course = $user->studentCourses()
            ->where('title', 'like', '%'.$needle.'%')
            ->orderBy('id')
            ->first();

        if ($course === null) {
            $this->error('Aucun cours dont le titre contient « '.$needle.' ».');

            return self::FAILURE;
        }

        try {
            $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($payload)) {
                throw new \JsonException('Racine JSON invalide');
            }
        } catch (\JsonException $e) {
            $this->error('JSON invalide : '.$e->getMessage());

            return self::FAILURE;
        }

        $quizTitle = (string) ($this->option('quiz-title') ?: 'Quiz importé');
        $max = (int) ($course->quizzes()->max('sort_order') ?? 0);
        $quiz = StudentCourseQuiz::query()->create([
            'student_course_id' => $course->id,
            'title' => $quizTitle,
            'instructions' => null,
            'sort_order' => $max + 1,
            'is_published' => true,
        ]);

        try {
            $importer->replaceQuizContent($quiz, $payload, $quizTitle);
        } catch (\Throwable $e) {
            $quiz->delete();
            $this->error('Import échoué : '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Quiz #'.$quiz->id.' attaché au cours #'.$course->id.' ('.$course->title.') — '.$quiz->questions()->count().' question(s).');

        return self::SUCCESS;
    }

    /** @param  array<string, mixed>  $data */
    private function validateSchedulePayload(array $data): void
    {
        $hasAny = ($data['schedule_weekday'] ?? null) !== null
            || ($data['schedule_time_start'] ?? null) !== null
            || ($data['schedule_time_end'] ?? null) !== null;

        if (! $hasAny) {
            return;
        }

        if (($data['schedule_weekday'] ?? null) === null
            || ($data['schedule_time_start'] ?? null) === null
            || ($data['schedule_time_end'] ?? null) === null) {
            throw ValidationException::withMessages([
                'schedule' => 'Créneau incomplet : fournis --weekday, --time-start et --time-end ensemble.',
            ]);
        }

        if (! preg_match('/^\d{2}:\d{2}$/', (string) $data['schedule_time_start'])
            || ! preg_match('/^\d{2}:\d{2}$/', (string) $data['schedule_time_end'])) {
            throw ValidationException::withMessages([
                'schedule' => 'Heures au format HH:MM (ex. 09:30).',
            ]);
        }

        if ($data['schedule_time_start'] >= $data['schedule_time_end']) {
            throw ValidationException::withMessages([
                'schedule' => 'L’heure de fin doit être après le début.',
            ]);
        }

        $d = (int) $data['schedule_weekday'];
        if ($d < 1 || $d > 7) {
            throw ValidationException::withMessages([
                'schedule' => '--weekday doit être entre 1 (lundi) et 7 (dimanche).',
            ]);
        }
    }
}
