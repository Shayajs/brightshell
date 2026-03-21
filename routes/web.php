<?php

use App\Http\Controllers\Account\HomeController as AccountHomeController;
use App\Http\Controllers\Admin\CompaniesController;
use App\Http\Controllers\Admin\CvAdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeclarationsController;
use App\Http\Controllers\Admin\InvoicesController;
use App\Http\Controllers\Admin\MembersController;
use App\Http\Controllers\Admin\RealisationsAdminController;
use App\Http\Controllers\Admin\StudentCourseQuizQuestionsController;
use App\Http\Controllers\Admin\StudentCourseQuizzesController;
use App\Http\Controllers\Admin\StudentCoursesController;
use App\Http\Controllers\Admin\StudentSubjectFilesController;
use App\Http\Controllers\Admin\StudentSubjectFoldersController;
use App\Http\Controllers\Admin\StudentSubjectsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Collabs\DashboardController as CollabsDashboardController;
use App\Http\Controllers\Courses\DashboardController as CoursesDashboardController;
use App\Http\Controllers\Courses\StudentCourseQuizController;
use App\Http\Controllers\Courses\StudentMaterialsController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RealisationsController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\Settings\DashboardController as SettingsDashboardController;
use App\Http\Controllers\Settings\NotificationPreferencesController;
use App\Http\Controllers\Settings\ProfileController as SettingsProfileController;
use App\Http\Controllers\Settings\SecurityController as SettingsSecurityController;
use App\Http\Controllers\Users\DashboardController as UsersDashboardController;
use App\Http\Middleware\EnsureUserCanAccessAdminPortal;
use App\Support\BrightshellDomain;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portail compte (auth) — sous-domaine account.*
|--------------------------------------------------------------------------
*/
$registerAuthRoutes = function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');
        if (config('brightshell.registration_open', false)) {
            Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
            Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1');
        }
    });
    Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');
};

/**
 * Domaine racine unique des sous-domaines portail.
 * Ex: APP_URL=https://brightshell.domain -> account/admin/collabs/... .brightshell.domain
 */
$inferredRoot = BrightshellDomain::effectiveRoot();

$accountHost = (string) config('brightshell.domains.account_host', '');
if ($accountHost === '' && $inferredRoot !== '') {
    $accountHost = 'account.'.$inferredRoot;
}

if ($accountHost !== '') {
    Route::domain($accountHost)->group(function () use ($registerAuthRoutes): void {
        Route::get('/', AccountHomeController::class)->name('account.home');
        $registerAuthRoutes();
    });
}

/*
|--------------------------------------------------------------------------
| Portail administration (sous-domaine admin.*)
|--------------------------------------------------------------------------
*/
$registerAdminRoutes = function (): void {
    Route::get('/', DashboardController::class)->name('admin.dashboard');

    // Membres
    Route::get('/members', [MembersController::class, 'index'])->name('admin.members.index');
    Route::get('/members/create', [MembersController::class, 'create'])->name('admin.members.create');
    Route::post('/members', [MembersController::class, 'store'])->name('admin.members.store');
    Route::get('/members/{member}', [MembersController::class, 'show'])->name('admin.members.show');
    Route::post('/members/{member}/roles', [MembersController::class, 'updateRoles'])->name('admin.members.roles');

    // Cours par élève (indépendants par utilisateur)
    Route::get('/student-courses', [StudentCoursesController::class, 'index'])->name('admin.student-courses.index');
    Route::get('/student-courses/student/{user}', [StudentCoursesController::class, 'student'])->name('admin.student-courses.student');
    Route::get('/student-courses/student/{user}/create', [StudentCoursesController::class, 'create'])->name('admin.student-courses.create');
    Route::post('/student-courses/student/{user}', [StudentCoursesController::class, 'store'])->name('admin.student-courses.store');
    Route::get('/student-courses/student/{user}/course/{student_course}/edit', [StudentCoursesController::class, 'edit'])->name('admin.student-courses.edit');
    Route::put('/student-courses/student/{user}/course/{student_course}', [StudentCoursesController::class, 'update'])->name('admin.student-courses.update');
    Route::delete('/student-courses/student/{user}/course/{student_course}', [StudentCoursesController::class, 'destroy'])->name('admin.student-courses.destroy');

    Route::get('/student-courses/student/{user}/course/{student_course}/quizzes', [StudentCourseQuizzesController::class, 'index'])->name('admin.student-course-quizzes.index');
    Route::get('/student-courses/student/{user}/course/{student_course}/quizzes/create', [StudentCourseQuizzesController::class, 'create'])->name('admin.student-course-quizzes.create');
    Route::post('/student-courses/student/{user}/course/{student_course}/quizzes', [StudentCourseQuizzesController::class, 'store'])->name('admin.student-course-quizzes.store');
    Route::get('/student-courses/student/{user}/course/{student_course}/quizzes/{quiz}/edit', [StudentCourseQuizzesController::class, 'edit'])->name('admin.student-course-quizzes.edit');
    Route::put('/student-courses/student/{user}/course/{student_course}/quizzes/{quiz}', [StudentCourseQuizzesController::class, 'update'])->name('admin.student-course-quizzes.update');
    Route::delete('/student-courses/student/{user}/course/{student_course}/quizzes/{quiz}', [StudentCourseQuizzesController::class, 'destroy'])->name('admin.student-course-quizzes.destroy');
    Route::post('/student-courses/student/{user}/course/{student_course}/quizzes/{quiz}/import-json', [StudentCourseQuizzesController::class, 'importJson'])->name('admin.student-course-quizzes.import-json');
    Route::post('/student-courses/student/{user}/course/{student_course}/quizzes/{quiz}/questions', [StudentCourseQuizQuestionsController::class, 'store'])->name('admin.student-course-quiz-questions.store');
    Route::delete('/student-courses/student/{user}/course/{student_course}/quizzes/{quiz}/questions/{question}', [StudentCourseQuizQuestionsController::class, 'destroy'])->name('admin.student-course-quiz-questions.destroy');

    // Matières / dossiers / fichiers (par élève, titres libres)
    Route::get('/student-subjects', [StudentSubjectsController::class, 'index'])->name('admin.student-subjects.index');
    Route::get('/student-subjects/student/{user}', [StudentSubjectsController::class, 'student'])->name('admin.student-subjects.student');
    Route::get('/student-subjects/student/{user}/create', [StudentSubjectsController::class, 'create'])->name('admin.student-subjects.create');
    Route::post('/student-subjects/student/{user}', [StudentSubjectsController::class, 'store'])->name('admin.student-subjects.store');
    Route::get('/student-subjects/student/{user}/subject/{student_subject}', [StudentSubjectsController::class, 'show'])->name('admin.student-subjects.show');
    Route::get('/student-subjects/student/{user}/subject/{student_subject}/edit', [StudentSubjectsController::class, 'edit'])->name('admin.student-subjects.edit');
    Route::put('/student-subjects/student/{user}/subject/{student_subject}', [StudentSubjectsController::class, 'update'])->name('admin.student-subjects.update');
    Route::delete('/student-subjects/student/{user}/subject/{student_subject}', [StudentSubjectsController::class, 'destroy'])->name('admin.student-subjects.destroy');

    Route::post('/student-subjects/student/{user}/subject/{student_subject}/folders', [StudentSubjectFoldersController::class, 'store'])->name('admin.student-subject-folders.store');
    Route::get('/student-subjects/student/{user}/subject/{student_subject}/folders/{folder}/edit', [StudentSubjectFoldersController::class, 'edit'])->name('admin.student-subject-folders.edit');
    Route::put('/student-subjects/student/{user}/subject/{student_subject}/folders/{folder}', [StudentSubjectFoldersController::class, 'update'])->name('admin.student-subject-folders.update');
    Route::delete('/student-subjects/student/{user}/subject/{student_subject}/folders/{folder}', [StudentSubjectFoldersController::class, 'destroy'])->name('admin.student-subject-folders.destroy');

    Route::post('/student-subjects/student/{user}/subject/{student_subject}/files', [StudentSubjectFilesController::class, 'store'])->name('admin.student-subject-files.store');
    Route::post('/student-subjects/student/{user}/subject/{student_subject}/files/markdown', [StudentSubjectFilesController::class, 'storeMarkdown'])->name('admin.student-subject-files.store-markdown');
    Route::post('/student-subjects/markdown/apercu-json', [StudentSubjectFilesController::class, 'previewMarkdownJson'])
        ->middleware('throttle:120,1')
        ->name('admin.student-subject-files.markdown.preview-json');
    Route::get('/student-subjects/student/{user}/subject/{student_subject}/folders/{folder}/markdown/nouveau', [StudentSubjectFilesController::class, 'createMarkdown'])->name('admin.student-subject-files.markdown.create');
    Route::get('/student-subjects/student/{user}/subject/{student_subject}/files/{file}/markdown/editer', [StudentSubjectFilesController::class, 'editMarkdown'])->name('admin.student-subject-files.markdown.edit');
    Route::put('/student-subjects/student/{user}/subject/{student_subject}/files/{file}/markdown', [StudentSubjectFilesController::class, 'updateMarkdown'])->name('admin.student-subject-files.markdown.update');
    Route::patch('/student-subjects/student/{user}/subject/{student_subject}/files/{file}/acces', [StudentSubjectFilesController::class, 'updateFileAccess'])->name('admin.student-subject-files.update-access');
    Route::get('/student-subjects/files/{file}/download', [StudentSubjectFilesController::class, 'download'])->name('admin.student-subject-files.download');
    Route::get('/student-subjects/files/{file}/apercu', [StudentSubjectFilesController::class, 'previewMarkdown'])->name('admin.student-subject-files.preview');
    Route::delete('/student-subjects/student/{user}/subject/{student_subject}/files/{file}', [StudentSubjectFilesController::class, 'destroy'])->name('admin.student-subject-files.destroy');

    // Sociétés
    Route::get('/companies', [CompaniesController::class, 'index'])->name('admin.companies.index');
    Route::get('/companies/create', [CompaniesController::class, 'create'])->name('admin.companies.create');
    Route::post('/companies', [CompaniesController::class, 'store'])->name('admin.companies.store');
    Route::get('/companies/{company}', [CompaniesController::class, 'show'])->name('admin.companies.show');
    Route::get('/companies/{company}/edit', [CompaniesController::class, 'edit'])->name('admin.companies.edit');
    Route::put('/companies/{company}', [CompaniesController::class, 'update'])->name('admin.companies.update');
    Route::delete('/companies/{company}', [CompaniesController::class, 'destroy'])->name('admin.companies.destroy');

    // Factures
    Route::get('/invoices', [InvoicesController::class, 'index'])->name('admin.invoices.index');
    Route::get('/invoices/create', [InvoicesController::class, 'create'])->name('admin.invoices.create');
    Route::post('/invoices', [InvoicesController::class, 'store'])->name('admin.invoices.store');
    Route::get('/invoices/{invoice}', [InvoicesController::class, 'show'])->name('admin.invoices.show');
    Route::get('/invoices/{invoice}/edit', [InvoicesController::class, 'edit'])->name('admin.invoices.edit');
    Route::put('/invoices/{invoice}', [InvoicesController::class, 'update'])->name('admin.invoices.update');
    Route::delete('/invoices/{invoice}', [InvoicesController::class, 'destroy'])->name('admin.invoices.destroy');

    // Déclarations (auto-entreprise / futur EI + TVA) + fiche entreprise
    Route::get('/declarations', [DeclarationsController::class, 'index'])->name('admin.declarations.index');
    Route::get('/declarations/urssaf', [DeclarationsController::class, 'urssaf'])->name('admin.declarations.urssaf');
    Route::get('/declarations/entreprise', [DeclarationsController::class, 'editBusiness'])->name('admin.declarations.business.edit');
    Route::put('/declarations/entreprise', [DeclarationsController::class, 'updateBusiness'])->name('admin.declarations.business.update');
    Route::redirect('/urssaf', '/declarations/urssaf')->name('admin.urssaf.index');

    // Réalisations
    Route::get('/realisations', [RealisationsAdminController::class, 'index'])->name('admin.realisations.index');
    Route::get('/realisations/create', [RealisationsAdminController::class, 'create'])->name('admin.realisations.create');
    Route::post('/realisations', [RealisationsAdminController::class, 'store'])->name('admin.realisations.store');
    Route::get('/realisations/{category}/{id}/edit', [RealisationsAdminController::class, 'edit'])->name('admin.realisations.edit');
    Route::put('/realisations/{category}/{id}', [RealisationsAdminController::class, 'update'])->name('admin.realisations.update');
    Route::delete('/realisations/{category}/{id}', [RealisationsAdminController::class, 'destroy'])->name('admin.realisations.destroy');
    Route::post('/realisations/reorder', [RealisationsAdminController::class, 'reorder'])->name('admin.realisations.reorder');

    // CV — overview + éditeurs par section
    Route::get('/cv', [CvAdminController::class, 'index'])->name('admin.cv.index');
    Route::get('/cv/contact', [CvAdminController::class, 'editContact'])->name('admin.cv.contact');
    Route::post('/cv/contact', [CvAdminController::class, 'updateContact'])->name('admin.cv.contact.update');
    Route::get('/cv/experience', [CvAdminController::class, 'editExperience'])->name('admin.cv.experience');
    Route::post('/cv/experience', [CvAdminController::class, 'updateExperience'])->name('admin.cv.experience.update');
    Route::get('/cv/diplomes', [CvAdminController::class, 'editDiplomes'])->name('admin.cv.diplomes');
    Route::post('/cv/diplomes', [CvAdminController::class, 'updateDiplomes'])->name('admin.cv.diplomes.update');
    Route::get('/cv/competences', [CvAdminController::class, 'editCompetences'])->name('admin.cv.competences');
    Route::post('/cv/competences', [CvAdminController::class, 'updateCompetences'])->name('admin.cv.competences.update');
    Route::get('/cv/certifications', [CvAdminController::class, 'editCertifications'])->name('admin.cv.certifications');
    Route::post('/cv/certifications', [CvAdminController::class, 'updateCertifications'])->name('admin.cv.certifications.update');
};

$adminHost = (string) config('brightshell.domains.admin_host', '');
if ($adminHost === '' && $inferredRoot !== '') {
    $adminHost = 'admin.'.$inferredRoot;
}

if ($adminHost !== '') {
    Route::domain($adminHost)
        ->middleware(['auth', EnsureUserCanAccessAdminPortal::class])
        ->group($registerAdminRoutes);
}

/*
|--------------------------------------------------------------------------
| Vitrine (domaine principal)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/services', [ServicesController::class, 'index'])->name('services');
Route::get('/realisations', [RealisationsController::class, 'index'])->name('realisations');
Route::get('/cv', [CvController::class, 'index'])->name('cv');

Route::permanentRedirect('/index.html', '/');
Route::permanentRedirect('/services.html', '/services');
Route::permanentRedirect('/realisations.html', '/realisations');
Route::permanentRedirect('/cv.html', '/cv');

/*
|--------------------------------------------------------------------------
| Portail collaborateurs (collabs.*)
|--------------------------------------------------------------------------
*/
$collabsHost = (string) config('brightshell.domains.collabs_host', '');
if ($collabsHost === '' && $inferredRoot !== '') {
    $collabsHost = 'collabs.'.$inferredRoot;
}
if ($collabsHost !== '') {
    Route::domain($collabsHost)
        ->middleware(['auth', 'roles.any:collaborator'])
        ->group(function (): void {
            Route::get('/', CollabsDashboardController::class)->name('portals.collabs');
        });
}

/*
|--------------------------------------------------------------------------
| Portail clients (users.*)
|--------------------------------------------------------------------------
*/
$usersHost = (string) config('brightshell.domains.users_host', '');
if ($usersHost === '' && $inferredRoot !== '') {
    $usersHost = 'users.'.$inferredRoot;
}
if ($usersHost !== '') {
    Route::domain($usersHost)
        ->middleware(['auth', 'roles.any:client'])
        ->group(function (): void {
            Route::get('/', UsersDashboardController::class)->name('portals.users');
        });
}

/*
|--------------------------------------------------------------------------
| Portail élèves (courses.*)
|--------------------------------------------------------------------------
*/
$coursesHost = (string) config('brightshell.domains.courses_host', '');
if ($coursesHost === '' && $inferredRoot !== '') {
    $coursesHost = 'courses.'.$inferredRoot;
}
if ($coursesHost !== '') {
    Route::domain($coursesHost)
        ->middleware(['auth', 'roles.any:student'])
        ->group(function (): void {
            Route::get('/matieres/fichiers/{file}/lire', [StudentMaterialsController::class, 'readMarkdown'])
                ->name('portals.courses.matieres.read');
            Route::get('/matieres/fichiers/{file}/telecharger', [StudentMaterialsController::class, 'download'])
                ->name('portals.courses.matieres.download');
            Route::get('/matieres', [StudentMaterialsController::class, 'index'])->name('portals.courses.matieres.index');
            Route::get('/matieres/{student_subject}', [StudentMaterialsController::class, 'show'])->name('portals.courses.matieres.show');
            Route::get('/cours/{student_course}/quiz/{quiz}', [StudentCourseQuizController::class, 'show'])->name('portals.courses.quiz.show');
            Route::post('/cours/{student_course}/quiz/{quiz}', [StudentCourseQuizController::class, 'submit'])->name('portals.courses.quiz.submit');
            Route::get('/', CoursesDashboardController::class)->name('portals.courses');
        });
}

/*
|--------------------------------------------------------------------------
| Portail réglages (settings.*)
|--------------------------------------------------------------------------
*/
$settingsHost = (string) config('brightshell.domains.settings_host', '');
if ($settingsHost === '' && $inferredRoot !== '') {
    $settingsHost = 'settings.'.$inferredRoot;
}
if ($settingsHost !== '') {
    Route::domain($settingsHost)
        ->middleware('auth')
        ->group(function (): void {
            Route::get('/', [SettingsDashboardController::class, 'index'])->name('portals.settings');

            Route::get('/profil', [SettingsProfileController::class, 'edit'])->name('portals.settings.profile.edit');
            Route::put('/profil', [SettingsProfileController::class, 'update'])->name('portals.settings.profile.update');

            Route::get('/notifications', [NotificationPreferencesController::class, 'edit'])->name('portals.settings.notifications.edit');
            Route::put('/notifications', [NotificationPreferencesController::class, 'update'])->name('portals.settings.notifications.update');
            Route::post('/notifications/lues', [NotificationPreferencesController::class, 'markAllRead'])->name('portals.settings.notifications.read-all');

            Route::get('/securite', [SettingsSecurityController::class, 'edit'])->name('portals.settings.security.edit');
            Route::put('/securite/mot-de-passe', [SettingsSecurityController::class, 'updatePassword'])->name('portals.settings.security.password');
            Route::delete('/securite/autres-sessions', [SettingsSecurityController::class, 'destroyOtherSessions'])->name('portals.settings.security.sessions.destroy-others');
        });
}

/*
|--------------------------------------------------------------------------
| Vitrine sur sous-domaines autorisés (ex. www) — account.* exclu
|--------------------------------------------------------------------------
*/
$rootDomain = BrightshellDomain::effectiveRoot();
$vitrineSubs = config('brightshell.domains.vitrine_subdomains');

if (is_string($rootDomain) && $rootDomain !== '' && is_array($vitrineSubs) && $vitrineSubs !== []) {
    Route::domain('{sub}.'.$rootDomain)
        ->whereIn('sub', $vitrineSubs)
        ->group(function (): void {
            Route::get('/', [HomeController::class, 'index'])->name('home.subdomain');
            Route::get('/services', [ServicesController::class, 'index'])->name('services.subdomain');
            Route::get('/realisations', [RealisationsController::class, 'index'])->name('realisations.subdomain');
            Route::get('/cv', [CvController::class, 'index'])->name('cv.subdomain');
        });
}

/*
|--------------------------------------------------------------------------
| Sous-domaine inconnu -> retour vitrine
|--------------------------------------------------------------------------
*/
if (is_string($rootDomain) && $rootDomain !== '') {
    $reservedSubs = array_values(array_unique(array_filter(array_merge(
        ['account', 'admin', 'api', 'collabs', 'users', 'courses', 'settings'],
        is_array($vitrineSubs) ? $vitrineSubs : []
    ))));

    $escapedSubs = array_map(
        static fn (string $sub): string => preg_quote($sub, '/'),
        $reservedSubs
    );

    $subPattern = '^(?!'.implode('$|^', $escapedSubs).'$)[a-z0-9-]+$';

    Route::domain('{sub}.'.$rootDomain)
        ->where(['sub' => $subPattern])
        ->group(function (): void {
            Route::fallback(function () {
                return redirect()->to(rtrim((string) config('app.url'), '/').'/');
            });
        });
}
