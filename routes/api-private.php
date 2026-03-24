<?php

use App\Http\Controllers\Api\V1\Admin\AdminCompaniesApiController;
use App\Http\Controllers\Api\V1\Admin\AdminDeclarationsApiController;
use App\Http\Controllers\Api\V1\Admin\AdminInvoicesApiController;
use App\Http\Controllers\Api\V1\Admin\AdminMembersApiController;
use App\Http\Controllers\Api\V1\Admin\AdminProjectsApiController;
use App\Http\Controllers\Api\V1\Admin\AdminSearchApiController;
use App\Http\Controllers\Api\V1\Admin\AdminSupportTicketsApiController;
use App\Http\Controllers\Api\V1\AuthTokensController;
use App\Http\Controllers\Api\V1\ClientCompaniesApiController;
use App\Http\Controllers\Api\V1\CollaboratorTeamsApiController;
use App\Http\Controllers\Api\V1\CoursesController;
use App\Http\Controllers\Api\V1\DocsApiController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\NotificationsApiController;
use App\Http\Controllers\Api\V1\Project\ProjectAppointmentsApiController;
use App\Http\Controllers\Api\V1\Project\ProjectContractsApiController;
use App\Http\Controllers\Api\V1\Project\ProjectDocumentsApiController;
use App\Http\Controllers\Api\V1\Project\ProjectKanbanApiController;
use App\Http\Controllers\Api\V1\Project\ProjectNotesApiController;
use App\Http\Controllers\Api\V1\Project\ProjectPriceItemsApiController;
use App\Http\Controllers\Api\V1\Project\ProjectRequestsApiController;
use App\Http\Controllers\Api\V1\Project\ProjectSpecSectionsApiController;
use App\Http\Controllers\Api\V1\Project\ProjectsApiController;
use App\Http\Controllers\Api\V1\SecurityApiController;
use App\Http\Controllers\Api\V1\StudentMaterialsController;
use App\Http\Controllers\Api\V1\StudentQuizzesApiController;
use App\Http\Controllers\Api\V1\SupportTicketsApiController;
use Illuminate\Support\Facades\Route;

Route::delete('/auth/token', [AuthTokensController::class, 'destroy'])->name('api.v1.auth.token.destroy');

Route::get('/me', [MeController::class, 'show'])->name('api.v1.me.show');
Route::put('/me', [MeController::class, 'update'])->name('api.v1.me.update');

Route::get('/notifications', [NotificationsApiController::class, 'show'])->name('api.v1.notifications.show');
Route::patch('/notifications', [NotificationsApiController::class, 'update'])->name('api.v1.notifications.update');
Route::post('/notifications/lues', [NotificationsApiController::class, 'markAllRead'])->name('api.v1.notifications.read-all');

Route::put('/securite/mot-de-passe', [SecurityApiController::class, 'updatePassword'])->name('api.v1.security.password');
Route::delete('/securite/autres-sessions', [SecurityApiController::class, 'destroyOtherSessions'])->name('api.v1.security.sessions.destroy-others');

Route::get('/courses', [CoursesController::class, 'index'])->name('api.v1.courses.index');
Route::get('/courses/{studentCourse}', [CoursesController::class, 'show'])->name('api.v1.courses.show');
Route::get('/courses/{studentCourse}/quizzes/{quiz}', [StudentQuizzesApiController::class, 'show'])->name('api.v1.courses.quizzes.show');
Route::post('/courses/{studentCourse}/quizzes/{quiz}/soumission', [StudentQuizzesApiController::class, 'submit'])->name('api.v1.courses.quizzes.submit');

Route::get('/matieres', [StudentMaterialsController::class, 'index'])->name('api.v1.matieres.index');
Route::get('/matieres/{studentSubject}', [StudentMaterialsController::class, 'show'])->name('api.v1.matieres.show');
Route::get('/fichiers/{file}/markdown', [StudentMaterialsController::class, 'markdown'])->name('api.v1.fichiers.markdown');
Route::get('/fichiers/{file}/telecharger', [StudentMaterialsController::class, 'download'])->name('api.v1.fichiers.download');

Route::get('/documentation/nav', [DocsApiController::class, 'nav'])->name('api.v1.documentation.nav');
Route::get('/documentation/pages/{path}', [DocsApiController::class, 'show'])
    ->where('path', '.*')
    ->name('api.v1.documentation.pages.show');

Route::get('/clients/societes', [ClientCompaniesApiController::class, 'index'])->name('api.v1.clients.companies.index');
Route::get('/clients/societes/{company}', [ClientCompaniesApiController::class, 'show'])->name('api.v1.clients.companies.show');
Route::put('/clients/societes/{company}', [ClientCompaniesApiController::class, 'update'])->name('api.v1.clients.companies.update');

Route::get('/mes-demandes-support', [SupportTicketsApiController::class, 'indexMine'])->name('api.v1.support-tickets.mine.index');
Route::get('/mes-demandes-support/{ticket}', [SupportTicketsApiController::class, 'showMine'])->name('api.v1.support-tickets.mine.show');
Route::post('/mes-demandes-support', [SupportTicketsApiController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('api.v1.support-tickets.mine.store');

Route::get('/collaborateurs/equipes', [CollaboratorTeamsApiController::class, 'index'])->name('api.v1.collabs.teams.index');
Route::get('/collaborateurs/equipes/capacites', [CollaboratorTeamsApiController::class, 'capabilitiesCatalog'])->name('api.v1.collabs.capabilities.index');
Route::get('/collaborateurs/equipes/{collab_team}', [CollaboratorTeamsApiController::class, 'show'])->name('api.v1.collabs.teams.show');
Route::put('/collaborateurs/equipes/{collab_team}/permissions', [CollaboratorTeamsApiController::class, 'updateCapabilities'])->name('api.v1.collabs.teams.permissions.update');
Route::post('/collaborateurs/equipes/{collab_team}/membres', [CollaboratorTeamsApiController::class, 'storeMember'])->name('api.v1.collabs.teams.members.store');
Route::delete('/collaborateurs/equipes/{collab_team}/membres/{user}', [CollaboratorTeamsApiController::class, 'destroyMember'])->name('api.v1.collabs.teams.members.destroy');
Route::patch('/collaborateurs/equipes/{collab_team}/membres/{user}/gerant', [CollaboratorTeamsApiController::class, 'updateMemberManager'])->name('api.v1.collabs.teams.members.manager');
Route::get('/collaborateurs/equipes/{collab_team}/messages', [CollaboratorTeamsApiController::class, 'messagesPoll'])->name('api.v1.collabs.teams.messages.poll');
Route::post('/collaborateurs/equipes/{collab_team}/messages', [CollaboratorTeamsApiController::class, 'messagesStore'])->name('api.v1.collabs.teams.messages.store');

Route::get('/projets', [ProjectsApiController::class, 'index'])->name('api.v1.projects.index');
Route::get('/projets/{project}', [ProjectsApiController::class, 'show'])->name('api.v1.projects.show');

Route::get('/projets/{project}/notes', [ProjectNotesApiController::class, 'index'])->name('api.v1.projects.notes.index');
Route::post('/projets/{project}/notes', [ProjectNotesApiController::class, 'store'])->name('api.v1.projects.notes.store');
Route::delete('/projets/{project}/notes/{note}', [ProjectNotesApiController::class, 'destroy'])->name('api.v1.projects.notes.destroy');

Route::get('/projets/{project}/demandes', [ProjectRequestsApiController::class, 'index'])->name('api.v1.projects.requests.index');
Route::post('/projets/{project}/demandes', [ProjectRequestsApiController::class, 'store'])->name('api.v1.projects.requests.store');
Route::put('/projets/{project}/demandes/{project_request}', [ProjectRequestsApiController::class, 'update'])->name('api.v1.projects.requests.update');
Route::delete('/projets/{project}/demandes/{project_request}', [ProjectRequestsApiController::class, 'destroy'])->name('api.v1.projects.requests.destroy');

Route::get('/projets/{project}/rendez-vous', [ProjectAppointmentsApiController::class, 'index'])->name('api.v1.projects.appointments.index');
Route::post('/projets/{project}/rendez-vous', [ProjectAppointmentsApiController::class, 'store'])->name('api.v1.projects.appointments.store');
Route::put('/projets/{project}/rendez-vous/{appointment}', [ProjectAppointmentsApiController::class, 'update'])->name('api.v1.projects.appointments.update');
Route::delete('/projets/{project}/rendez-vous/{appointment}', [ProjectAppointmentsApiController::class, 'destroy'])->name('api.v1.projects.appointments.destroy');

Route::get('/projets/{project}/kanban', [ProjectKanbanApiController::class, 'show'])->name('api.v1.projects.kanban.show');
Route::post('/projets/{project}/kanban/colonnes', [ProjectKanbanApiController::class, 'storeColumn'])->name('api.v1.projects.kanban.columns.store');
Route::delete('/projets/{project}/kanban/colonnes/{column}', [ProjectKanbanApiController::class, 'destroyColumn'])->name('api.v1.projects.kanban.columns.destroy');
Route::post('/projets/{project}/kanban/colonnes/{column}/cartes', [ProjectKanbanApiController::class, 'storeCard'])->name('api.v1.projects.kanban.cards.store');
Route::post('/projets/{project}/kanban/cartes/{card}/deplacer', [ProjectKanbanApiController::class, 'moveCard'])->name('api.v1.projects.kanban.cards.move');
Route::delete('/projets/{project}/kanban/cartes/{card}', [ProjectKanbanApiController::class, 'destroyCard'])->name('api.v1.projects.kanban.cards.destroy');

Route::get('/projets/{project}/documents', [ProjectDocumentsApiController::class, 'index'])->name('api.v1.projects.documents.index');
Route::post('/projets/{project}/documents', [ProjectDocumentsApiController::class, 'store'])->name('api.v1.projects.documents.store');
Route::delete('/projets/{project}/documents/{document}', [ProjectDocumentsApiController::class, 'destroy'])->name('api.v1.projects.documents.destroy');
Route::get('/projets/{project}/documents/{document}/telecharger', [ProjectDocumentsApiController::class, 'download'])->name('api.v1.projects.documents.download');

Route::get('/projets/{project}/cahier-des-charges', [ProjectSpecSectionsApiController::class, 'index'])->name('api.v1.projects.specs.index');
Route::post('/projets/{project}/cahier-des-charges', [ProjectSpecSectionsApiController::class, 'store'])->name('api.v1.projects.specs.store');
Route::put('/projets/{project}/cahier-des-charges/{section}', [ProjectSpecSectionsApiController::class, 'update'])->name('api.v1.projects.specs.update');
Route::delete('/projets/{project}/cahier-des-charges/{section}', [ProjectSpecSectionsApiController::class, 'destroy'])->name('api.v1.projects.specs.destroy');

Route::get('/projets/{project}/contrats', [ProjectContractsApiController::class, 'index'])->name('api.v1.projects.contracts.index');
Route::post('/projets/{project}/contrats', [ProjectContractsApiController::class, 'store'])->name('api.v1.projects.contracts.store');
Route::put('/projets/{project}/contrats/{contract}', [ProjectContractsApiController::class, 'update'])->name('api.v1.projects.contracts.update');
Route::delete('/projets/{project}/contrats/{contract}', [ProjectContractsApiController::class, 'destroy'])->name('api.v1.projects.contracts.destroy');

Route::get('/projets/{project}/prix', [ProjectPriceItemsApiController::class, 'index'])->name('api.v1.projects.prices.index');
Route::post('/projets/{project}/prix', [ProjectPriceItemsApiController::class, 'store'])->name('api.v1.projects.prices.store');
Route::put('/projets/{project}/prix/{item}', [ProjectPriceItemsApiController::class, 'update'])->name('api.v1.projects.prices.update');
Route::delete('/projets/{project}/prix/{item}', [ProjectPriceItemsApiController::class, 'destroy'])->name('api.v1.projects.prices.destroy');

Route::prefix('admin')->group(function (): void {
    Route::get('/recherche', AdminSearchApiController::class)->middleware('throttle:60,1')->name('api.v1.admin.search');

    Route::get('/factures', [AdminInvoicesApiController::class, 'index'])->name('api.v1.admin.invoices.index');
    Route::post('/factures', [AdminInvoicesApiController::class, 'store'])->name('api.v1.admin.invoices.store');
    Route::get('/factures/{invoice}', [AdminInvoicesApiController::class, 'show'])->name('api.v1.admin.invoices.show');
    Route::put('/factures/{invoice}', [AdminInvoicesApiController::class, 'update'])->name('api.v1.admin.invoices.update');
    Route::delete('/factures/{invoice}', [AdminInvoicesApiController::class, 'destroy'])->name('api.v1.admin.invoices.destroy');

    Route::get('/demandes-support', [AdminSupportTicketsApiController::class, 'index'])->name('api.v1.admin.support-tickets.index');
    Route::get('/demandes-support/{ticket}', [AdminSupportTicketsApiController::class, 'show'])->name('api.v1.admin.support-tickets.show');
    Route::patch('/demandes-support/{ticket}', [AdminSupportTicketsApiController::class, 'update'])->name('api.v1.admin.support-tickets.update');
    Route::post('/demandes-support/{ticket}/verifier-email-membre', [AdminSupportTicketsApiController::class, 'verifyMemberEmail'])->name('api.v1.admin.support-tickets.verify-email');

    Route::get('/membres', [AdminMembersApiController::class, 'index'])->name('api.v1.admin.members.index');
    Route::get('/membres/{member}', [AdminMembersApiController::class, 'show'])->name('api.v1.admin.members.show');

    Route::get('/societes', [AdminCompaniesApiController::class, 'index'])->name('api.v1.admin.companies.index');

    Route::get('/declarations/entreprise', [AdminDeclarationsApiController::class, 'businessProfile'])->name('api.v1.admin.declarations.business.show');
    Route::put('/declarations/entreprise', [AdminDeclarationsApiController::class, 'updateBusinessProfile'])->name('api.v1.admin.declarations.business.update');
    Route::get('/declarations/urssaf', [AdminDeclarationsApiController::class, 'urssafSummary'])->name('api.v1.admin.declarations.urssaf');

    Route::get('/projets/meta', [AdminProjectsApiController::class, 'formMeta'])->name('api.v1.admin.projects.meta');
    Route::get('/projets', [AdminProjectsApiController::class, 'index'])->name('api.v1.admin.projects.index');
    Route::post('/projets', [AdminProjectsApiController::class, 'store'])->name('api.v1.admin.projects.store');
    Route::get('/projets/{project}', [AdminProjectsApiController::class, 'show'])->name('api.v1.admin.projects.show');
    Route::put('/projets/{project}', [AdminProjectsApiController::class, 'update'])->name('api.v1.admin.projects.update');
    Route::delete('/projets/{project}', [AdminProjectsApiController::class, 'destroy'])->name('api.v1.admin.projects.destroy');
    Route::post('/projets/{project}/archiver', [AdminProjectsApiController::class, 'archive'])->name('api.v1.admin.projects.archive');
    Route::post('/projets/{project}/reactiver', [AdminProjectsApiController::class, 'unarchive'])->name('api.v1.admin.projects.unarchive');
    Route::post('/projets/{project}/membres', [AdminProjectsApiController::class, 'attachMember'])->name('api.v1.admin.projects.members.attach');
    Route::put('/projets/{project}/membres/{user}', [AdminProjectsApiController::class, 'updateMember'])->name('api.v1.admin.projects.members.update');
    Route::delete('/projets/{project}/membres/{user}', [AdminProjectsApiController::class, 'detachMember'])->name('api.v1.admin.projects.members.detach');
});
