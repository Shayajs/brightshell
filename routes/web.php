<?php

use App\Http\Controllers\Account\HomeController as AccountHomeController;
use App\Http\Controllers\Admin\AdminAuditLogsController;
use App\Http\Controllers\Admin\ApiManagerController;
use App\Http\Controllers\Admin\ClientsController;
use App\Http\Controllers\Admin\CollaboratorsController;
use App\Http\Controllers\Admin\CollaboratorTeamsController;
use App\Http\Controllers\Admin\CompaniesController;
use App\Http\Controllers\Admin\CvAdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeclarationsController;
use App\Http\Controllers\Admin\DocNodesController;
use App\Http\Controllers\Admin\InvoicesController;
use App\Http\Controllers\Admin\MailTemplatesController;
use App\Http\Controllers\Admin\MembersController;
use App\Http\Controllers\Admin\ProjectInvitationsController;
use App\Http\Controllers\Admin\ProjectsController;
use App\Http\Controllers\Admin\RealisationsAdminController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SiteAppearanceController;
use App\Http\Controllers\Admin\StudentCourseQuizQuestionsController;
use App\Http\Controllers\Admin\StudentCourseQuizzesController;
use App\Http\Controllers\Admin\StudentCoursesController;
use App\Http\Controllers\Admin\StudentSubjectFilesController;
use App\Http\Controllers\Admin\StudentSubjectFoldersController;
use App\Http\Controllers\Admin\StudentSubjectsController;
use App\Http\Controllers\Admin\SupportTicketsController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\Auth\AcceptProjectInvitationController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SupportTicketFromVerificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Collabs\DashboardController as CollabsDashboardController;
use App\Http\Controllers\Collabs\TeamMembersController as CollabsTeamMembersController;
use App\Http\Controllers\Collabs\TeamMessagesController as CollabsTeamMessagesController;
use App\Http\Controllers\Collabs\TeamPermissionsController as CollabsTeamPermissionsController;
use App\Http\Controllers\Collabs\TeamsController as CollabsTeamsController;
use App\Http\Controllers\Courses\DashboardController as CoursesDashboardController;
use App\Http\Controllers\Courses\StudentCourseQuizController;
use App\Http\Controllers\Courses\StudentMaterialsController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\Docs\DashboardController as DocsDashboardController;
use App\Http\Controllers\Docs\PageController as DocsPageController;
use App\Http\Controllers\Home\DashboardController as HomePortalDashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Project\AppointmentsController as ProjectAppointmentsController;
use App\Http\Controllers\Project\ContractsController as ProjectContractsController;
use App\Http\Controllers\Project\CreateProjectController as ProjectPortalCreateController;
use App\Http\Controllers\Project\DashboardController as ProjectPortalDashboardController;
use App\Http\Controllers\Project\DocumentsController as ProjectDocumentsController;
use App\Http\Controllers\Project\KanbanController as ProjectKanbanController;
use App\Http\Controllers\Project\NotesController as ProjectNotesController;
use App\Http\Controllers\Project\PriceItemsController as ProjectPriceItemsController;
use App\Http\Controllers\Project\RequestsController as ProjectRequestsController;
use App\Http\Controllers\Project\SettingsController as ProjectPortalSettingsController;
use App\Http\Controllers\Project\ShowController as ProjectPortalShowController;
use App\Http\Controllers\Project\SpecSectionsController as ProjectSpecSectionsController;
use App\Http\Controllers\RealisationsController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\Settings\AccountClosureController;
use App\Http\Controllers\Settings\ApiTokensController;
use App\Http\Controllers\Settings\DashboardController as SettingsDashboardController;
use App\Http\Controllers\Settings\NotificationPreferencesController;
use App\Http\Controllers\Settings\ProfileController as SettingsProfileController;
use App\Http\Controllers\Settings\SecurityController as SettingsSecurityController;
use App\Http\Controllers\Settings\SupportTicketController as SettingsSupportTicketController;
use App\Http\Controllers\Users\CompaniesController as UsersCompaniesController;
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
    Route::get('/project-invitation/{token}', AcceptProjectInvitationController::class)
        ->name('project-invitation.accept');

    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');
        if (config('brightshell.registration_open', false)) {
            Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
            Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1');
        }
    });
    Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

    Route::middleware('auth')->group(function (): void {
        Route::get('/email/verify', [EmailVerificationPromptController::class, 'show'])->name('verification.notice');
        Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)->middleware('signed')->name('verification.verify');
        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
        Route::post('/email/verify/support-ticket', [SupportTicketFromVerificationController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('verification.support-ticket');
    });
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
| Hub portail connecté (home.*) — sans nav latérale, switcher dans l’entête
|--------------------------------------------------------------------------
*/
$homeHost = (string) config('brightshell.domains.home_host', '');
if ($homeHost === '' && $inferredRoot !== '') {
    $homeHost = 'home.'.$inferredRoot;
}
if ($homeHost !== '') {
    Route::domain($homeHost)
        ->middleware(['auth', 'verified'])
        ->group(function (): void {
            Route::get('/', HomePortalDashboardController::class)->name('portals.home');
        });
}

/*
|--------------------------------------------------------------------------
| Portail administration (sous-domaine admin.*)
|--------------------------------------------------------------------------
*/
$registerAdminRoutes = function (): void {
    Route::get('/', DashboardController::class)->name('admin.dashboard');
    Route::get('/recherche', SearchController::class)->middleware('throttle:60,1')->name('admin.search');

    // Membres
    Route::get('/clients', [ClientsController::class, 'index'])->name('admin.clients.index');

    Route::get('/members', [MembersController::class, 'index'])->name('admin.members.index');
    Route::get('/members/create', [MembersController::class, 'create'])->name('admin.members.create');
    Route::post('/members', [MembersController::class, 'store'])->name('admin.members.store');
    Route::post('/members/{member}/archive', [MembersController::class, 'archive'])->name('admin.members.archive');
    Route::post('/members/{member}/restore', [MembersController::class, 'restore'])->name('admin.members.restore');
    Route::delete('/members/{member}/supprimer-definitif', [MembersController::class, 'forceDestroy'])->name('admin.members.force-destroy');

    Route::get('/members/{member}', [MembersController::class, 'show'])->name('admin.members.show');
    Route::post('/members/{member}/verify-email', [MembersController::class, 'verifyEmail'])->name('admin.members.verify-email');
    Route::post('/members/{member}/roles', [MembersController::class, 'updateRoles'])->name('admin.members.roles');
    Route::post('/members/{member}/collaborateur-acces', [MembersController::class, 'updateCollaboratorAccess'])->name('admin.members.collaborator-access');

    Route::get('/collaborateurs', [CollaboratorsController::class, 'index'])->name('admin.collaborators.index');
    Route::get('/collaborateurs/groupes', [CollaboratorTeamsController::class, 'index'])->name('admin.collaborator-teams.index');
    Route::get('/collaborateurs/groupes/creer', [CollaboratorTeamsController::class, 'create'])->name('admin.collaborator-teams.create');
    Route::post('/collaborateurs/groupes', [CollaboratorTeamsController::class, 'store'])->name('admin.collaborator-teams.store');
    Route::get('/collaborateurs/groupes/{collaborator_team}/editer', [CollaboratorTeamsController::class, 'edit'])->name('admin.collaborator-teams.edit');
    Route::put('/collaborateurs/groupes/{collaborator_team}', [CollaboratorTeamsController::class, 'update'])->name('admin.collaborator-teams.update');
    Route::delete('/collaborateurs/groupes/{collaborator_team}', [CollaboratorTeamsController::class, 'destroy'])->name('admin.collaborator-teams.destroy');
    Route::post('/collaborateurs/groupes/{collaborator_team}/membres', [CollaboratorTeamsController::class, 'storeMember'])->name('admin.collaborator-teams.members.store');
    Route::delete('/collaborateurs/groupes/{collaborator_team}/membres/{user}', [CollaboratorTeamsController::class, 'destroyMember'])->name('admin.collaborator-teams.members.destroy');
    Route::patch('/collaborateurs/groupes/{collaborator_team}/membres/{user}/gerant', [CollaboratorTeamsController::class, 'updateMemberManager'])->name('admin.collaborator-teams.members.manager');

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

    // Projets (portail project.* — invitations et droits réservés admin)
    Route::get('/projects', [ProjectsController::class, 'index'])->name('admin.projects.index');
    Route::get('/projects/create', [ProjectsController::class, 'create'])->name('admin.projects.create');
    Route::post('/projects', [ProjectsController::class, 'store'])->name('admin.projects.store');
    Route::get('/projects/{project}/edit', [ProjectsController::class, 'edit'])->name('admin.projects.edit');
    Route::put('/projects/{project}', [ProjectsController::class, 'update'])->name('admin.projects.update');
    Route::delete('/projects/{project}', [ProjectsController::class, 'destroy'])->name('admin.projects.destroy');
    Route::post('/projects/{project}/archiver', [ProjectsController::class, 'archive'])->name('admin.projects.archive');
    Route::post('/projects/{project}/reactiver', [ProjectsController::class, 'unarchive'])->name('admin.projects.unarchive');
    Route::post('/projects/{project}/membres', [ProjectsController::class, 'attachMember'])->name('admin.projects.members.attach');
    Route::put('/projects/{project}/membres/{user}', [ProjectsController::class, 'updateMember'])->name('admin.projects.members.update');
    Route::delete('/projects/{project}/membres/{user}', [ProjectsController::class, 'detachMember'])->name('admin.projects.members.detach');
    Route::post('/projects/{project}/inviter-email', [ProjectsController::class, 'inviteByEmail'])
        ->middleware('throttle:15,1')
        ->name('admin.projects.invite-email');

    Route::get('/invitations-projets', [ProjectInvitationsController::class, 'index'])->name('admin.project-invitations.index');
    Route::post('/invitations-projets/{project_invitation}/renvoyer', [ProjectInvitationsController::class, 'resend'])
        ->middleware('throttle:12,1')
        ->name('admin.project-invitations.resend');
    Route::delete('/invitations-projets/{project_invitation}', [ProjectInvitationsController::class, 'destroy'])->name('admin.project-invitations.destroy');

    Route::get('/journal', [AdminAuditLogsController::class, 'index'])->name('admin.audit-logs.index');
    Route::get('/sante', SystemHealthController::class)->name('admin.system-health');

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

    Route::get('/support-tickets', [SupportTicketsController::class, 'index'])->name('admin.support-tickets.index');
    Route::get('/support-tickets/{ticket}', [SupportTicketsController::class, 'show'])->name('admin.support-tickets.show');
    Route::patch('/support-tickets/{ticket}', [SupportTicketsController::class, 'update'])->name('admin.support-tickets.update');
    Route::post('/support-tickets/{ticket}/verify-email', [SupportTicketsController::class, 'verifyMemberEmail'])->name('admin.support-tickets.verify-email');

    Route::get('/documentation', [DocNodesController::class, 'index'])->name('admin.doc-nodes.index');
    Route::get('/documentation/creer', [DocNodesController::class, 'create'])->name('admin.doc-nodes.create');
    Route::post('/documentation', [DocNodesController::class, 'store'])->name('admin.doc-nodes.store');
    Route::get('/documentation/{docNode}/modifier', [DocNodesController::class, 'edit'])->name('admin.doc-nodes.edit');
    Route::put('/documentation/{docNode}', [DocNodesController::class, 'update'])->name('admin.doc-nodes.update');
    Route::delete('/documentation/{docNode}', [DocNodesController::class, 'destroy'])->name('admin.doc-nodes.destroy');

    Route::get('/api-publique', [ApiManagerController::class, 'index'])->name('admin.api-manager.index');
    Route::get('/mail-templates', [MailTemplatesController::class, 'index'])->name('admin.mail-templates.index');
    Route::get('/mail-templates/{key}', [MailTemplatesController::class, 'edit'])->name('admin.mail-templates.edit');
    Route::put('/mail-templates/{key}', [MailTemplatesController::class, 'update'])->name('admin.mail-templates.update');
    Route::post('/mail-templates/{key}/preview', [MailTemplatesController::class, 'preview'])->name('admin.mail-templates.preview');

    Route::get('/identite-site', [SiteAppearanceController::class, 'edit'])->name('admin.site-appearance.edit');
    Route::put('/identite-site', [SiteAppearanceController::class, 'update'])->name('admin.site-appearance.update');
    Route::post('/identite-site/reinitialiser-theme-mail', [SiteAppearanceController::class, 'resetMailTheme'])->name('admin.site-appearance.reset-mail-theme');

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
        ->middleware(['auth', 'verified', EnsureUserCanAccessAdminPortal::class])
        ->group($registerAdminRoutes);
}

/*
|--------------------------------------------------------------------------
| Vitrine (domaine principal)
|--------------------------------------------------------------------------
| Sans contrainte d’hôte : évite de servir la vitrine sur api.* (middleware).
*/
Route::middleware(['block.web.on.api.host'])->group(function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/services', [ServicesController::class, 'index'])->name('services');
    Route::get('/realisations', [RealisationsController::class, 'index'])->name('realisations');
    Route::get('/cv', [CvController::class, 'index'])->name('cv');

    Route::permanentRedirect('/index.html', '/');
    Route::permanentRedirect('/services.html', '/services');
    Route::permanentRedirect('/realisations.html', '/realisations');
    Route::permanentRedirect('/cv.html', '/cv');
});

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
        ->middleware(['auth', 'verified', 'roles.any:collaborator'])
        ->group(function (): void {
            Route::get('/', CollabsDashboardController::class)->name('portals.collabs');
            Route::get('/equipes', [CollabsTeamsController::class, 'index'])->name('portals.collabs.teams.index');
            Route::get('/equipes/{collab_team}', [CollabsTeamsController::class, 'show'])->name('portals.collabs.teams.show');
            Route::post('/equipes/{collab_team}/membres', [CollabsTeamMembersController::class, 'store'])->name('portals.collabs.teams.members.store');
            Route::delete('/equipes/{collab_team}/membres/{user}', [CollabsTeamMembersController::class, 'destroy'])
                ->name('portals.collabs.teams.members.destroy');
            Route::patch('/equipes/{collab_team}/membres/{user}/gerant', [CollabsTeamMembersController::class, 'updateManager'])
                ->name('portals.collabs.teams.members.manager');
            Route::get('/equipes/{collab_team}/permissions', [CollabsTeamPermissionsController::class, 'edit'])->name('portals.collabs.teams.permissions.edit');
            Route::put('/equipes/{collab_team}/permissions', [CollabsTeamPermissionsController::class, 'update'])->name('portals.collabs.teams.permissions.update');
            Route::get('/equipes/{collab_team}/messages', [CollabsTeamMessagesController::class, 'index'])->name('portals.collabs.teams.messages');
            Route::get('/equipes/{collab_team}/messages/api', [CollabsTeamMessagesController::class, 'poll'])->name('portals.collabs.teams.messages.poll');
            Route::post('/equipes/{collab_team}/messages/api', [CollabsTeamMessagesController::class, 'store'])->name('portals.collabs.teams.messages.store');
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
        ->middleware(['auth', 'verified', 'roles.any:client'])
        ->group(function (): void {
            Route::get('/', UsersDashboardController::class)->name('portals.users');
            Route::get('/societes', [UsersCompaniesController::class, 'index'])->name('portals.users.companies.index');
            Route::get('/societes/{company}', [UsersCompaniesController::class, 'show'])->name('portals.users.companies.show');
            Route::put('/societes/{company}', [UsersCompaniesController::class, 'update'])->name('portals.users.companies.update');
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
        ->middleware(['auth', 'verified', 'roles.any:student'])
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
        ->middleware(['auth', 'verified'])
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

            Route::get('/demande', [SettingsSupportTicketController::class, 'create'])->name('portals.settings.support-ticket.create');
            Route::post('/demande', [SettingsSupportTicketController::class, 'store'])
                ->middleware('throttle:10,1')
                ->name('portals.settings.support-ticket.store');

            Route::get('/compte/archiver', [AccountClosureController::class, 'edit'])->name('portals.settings.account.archive');
            Route::delete('/compte', [AccountClosureController::class, 'destroy'])->name('portals.settings.account.destroy');

            Route::middleware('role.developer')->group(function (): void {
                Route::get('/api', [ApiTokensController::class, 'index'])->name('portals.settings.api.index');
                Route::post('/api/jetons', [ApiTokensController::class, 'store'])->name('portals.settings.api.tokens.store');
                Route::delete('/api/jetons/{token}', [ApiTokensController::class, 'destroy'])->name('portals.settings.api.tokens.destroy');
            });
        });
}

/*
|--------------------------------------------------------------------------
| Portail projets (project.*)
|--------------------------------------------------------------------------
*/
$projectHost = (string) config('brightshell.domains.project_host', '');
if ($projectHost === '' && $inferredRoot !== '') {
    $projectHost = 'project.'.$inferredRoot;
}
if ($projectHost !== '') {
    Route::domain($projectHost)
        ->middleware(['auth', 'verified', 'portal.project'])
        ->group(function (): void {
            Route::get('/', ProjectPortalDashboardController::class)->name('portals.project');
            Route::get('/parametres', ProjectPortalSettingsController::class)->name('portals.project.settings');

            Route::get('/projets/nouveau', [ProjectPortalCreateController::class, 'create'])
                ->middleware('can:create,App\Models\Project')
                ->name('portals.project.create');
            Route::post('/projets', [ProjectPortalCreateController::class, 'store'])
                ->middleware('can:create,App\Models\Project')
                ->name('portals.project.store');

            Route::prefix('projets/{project}')
                ->middleware(['can:view,project'])
                ->group(function (): void {
                    Route::get('/', ProjectPortalShowController::class)->name('portals.project.show');

                    Route::get('/rendez-vous', [ProjectAppointmentsController::class, 'index'])->name('portals.project.appointments.index');
                    Route::post('/rendez-vous', [ProjectAppointmentsController::class, 'store'])->middleware('can:update,project')->name('portals.project.appointments.store');
                    Route::put('/rendez-vous/{appointment}', [ProjectAppointmentsController::class, 'update'])->middleware('can:update,project')->name('portals.project.appointments.update');
                    Route::delete('/rendez-vous/{appointment}', [ProjectAppointmentsController::class, 'destroy'])->middleware('can:update,project')->name('portals.project.appointments.destroy');

                    Route::get('/notes', [ProjectNotesController::class, 'index'])->name('portals.project.notes.index');
                    Route::post('/notes', [ProjectNotesController::class, 'store'])->name('portals.project.notes.store');
                    Route::delete('/notes/{note}', [ProjectNotesController::class, 'destroy'])->name('portals.project.notes.destroy');

                    Route::get('/kanban', [ProjectKanbanController::class, 'index'])->name('portals.project.kanban.index');
                    Route::post('/kanban/colonnes', [ProjectKanbanController::class, 'storeColumn'])->middleware('can:update,project')->name('portals.project.kanban.columns.store');
                    Route::delete('/kanban/colonnes/{column}', [ProjectKanbanController::class, 'destroyColumn'])->middleware('can:update,project')->name('portals.project.kanban.columns.destroy');
                    Route::post('/kanban/colonnes/{column}/cartes', [ProjectKanbanController::class, 'storeCard'])->middleware('can:update,project')->name('portals.project.kanban.cards.store');
                    Route::post('/kanban/cartes/{card}/deplacer', [ProjectKanbanController::class, 'moveCard'])->middleware('can:update,project')->name('portals.project.kanban.cards.move');
                    Route::delete('/kanban/cartes/{card}', [ProjectKanbanController::class, 'destroyCard'])->middleware('can:update,project')->name('portals.project.kanban.cards.destroy');

                    Route::get('/documents', [ProjectDocumentsController::class, 'index'])->name('portals.project.documents.index');
                    Route::post('/documents', [ProjectDocumentsController::class, 'store'])->middleware('can:update,project')->name('portals.project.documents.store');
                    Route::delete('/documents/{document}', [ProjectDocumentsController::class, 'destroy'])->middleware('can:update,project')->name('portals.project.documents.destroy');
                    Route::get('/documents/{document}/telecharger', [ProjectDocumentsController::class, 'download'])
                        ->middleware(['can:download,project'])
                        ->name('portals.project.documents.download');

                    Route::get('/cahier-des-charges', [ProjectSpecSectionsController::class, 'index'])->name('portals.project.specs.index');
                    Route::post('/cahier-des-charges', [ProjectSpecSectionsController::class, 'store'])->middleware('can:update,project')->name('portals.project.specs.store');
                    Route::put('/cahier-des-charges/{section}', [ProjectSpecSectionsController::class, 'update'])->middleware('can:update,project')->name('portals.project.specs.update');
                    Route::delete('/cahier-des-charges/{section}', [ProjectSpecSectionsController::class, 'destroy'])->middleware('can:update,project')->name('portals.project.specs.destroy');

                    Route::get('/contrats', [ProjectContractsController::class, 'index'])->name('portals.project.contracts.index');
                    Route::post('/contrats', [ProjectContractsController::class, 'store'])->middleware('can:update,project')->name('portals.project.contracts.store');
                    Route::put('/contrats/{contract}', [ProjectContractsController::class, 'update'])->middleware('can:update,project')->name('portals.project.contracts.update');
                    Route::delete('/contrats/{contract}', [ProjectContractsController::class, 'destroy'])->middleware('can:update,project')->name('portals.project.contracts.destroy');

                    Route::get('/prix', [ProjectPriceItemsController::class, 'index'])->name('portals.project.prices.index');
                    Route::post('/prix', [ProjectPriceItemsController::class, 'store'])->middleware('can:update,project')->name('portals.project.prices.store');
                    Route::put('/prix/{item}', [ProjectPriceItemsController::class, 'update'])->middleware('can:update,project')->name('portals.project.prices.update');
                    Route::delete('/prix/{item}', [ProjectPriceItemsController::class, 'destroy'])->middleware('can:update,project')->name('portals.project.prices.destroy');

                    Route::get('/demandes', [ProjectRequestsController::class, 'index'])->name('portals.project.requests.index');
                    Route::post('/demandes', [ProjectRequestsController::class, 'store'])->name('portals.project.requests.store');
                    Route::put('/demandes/{project_request}', [ProjectRequestsController::class, 'update'])->middleware('can:update,project')->name('portals.project.requests.update');
                    Route::delete('/demandes/{project_request}', [ProjectRequestsController::class, 'destroy'])->middleware('can:update,project')->name('portals.project.requests.destroy');
                });
        });
}

/*
|--------------------------------------------------------------------------
| Portail documentation (docs.*)
|--------------------------------------------------------------------------
*/
$docsHost = (string) config('brightshell.domains.docs_host', '');
if ($docsHost === '' && $inferredRoot !== '') {
    $docsHost = 'docs.'.$inferredRoot;
}
if ($docsHost !== '') {
    Route::domain($docsHost)
        ->middleware(['auth', 'verified'])
        ->group(function (): void {
            Route::get('/', DocsDashboardController::class)->name('portals.docs');
            Route::get('/{path}', [DocsPageController::class, 'show'])
                ->where('path', '.+')
                ->name('portals.docs.show');
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
        ['account', 'admin', 'api', 'collabs', 'users', 'courses', 'settings', 'docs', 'home', 'project'],
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
