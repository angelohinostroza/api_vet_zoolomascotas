<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Rol\RoleController;
use App\Http\Controllers\Pets\PetsController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Surgerie\SurgerieController;
use App\Http\Controllers\MedicalRecord\PaymentController;
use App\Http\Controllers\Appointment\AppointmentController;
use App\Http\Controllers\Kpi\KpiController;
use App\Http\Controllers\Vaccination\VaccinationController;
use App\Http\Controllers\Veterinarie\VeterinarieController;
use App\Http\Controllers\MedicalRecord\MedicalRecordController;
use App\Http\Controllers\Owners\OwnerController;
use App\Http\Controllers\DashboardApp\DashboardAppController;

Route::group([
    //'middleware' => 'api',
    'prefix' => 'auth',
    //'middleware' => ['auth:api']
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');//->middleware('auth:api')
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');//->middleware('auth:api')
    Route::post('/me', [AuthController::class, 'me'])->name('me');//->middleware('auth:api')
});
Route::group([
    'middleware' => ['auth:api']
], function ($router) {
    Route::resource("role", RoleController::class);
    Route::post("staffs/{id}", [StaffController::class, "update"]);
    Route::resource("staffs", StaffController::class);

    Route::get("veterinaries/config", [VeterinarieController::class, "config"]);
    Route::post("veterinaries/{id}", [VeterinarieController::class, "update"]);
    Route::resource("veterinaries", VeterinarieController::class);

    Route::post("pets/{id}", [PetsController::class, "update"]);
    Route::resource("pets", PetsController::class);

    Route::get("appointments/search-pets/{search}", [AppointmentController::class, "searchPets"]);
    Route::post("appointments/filter-availability", [AppointmentController::class, "filter"]);
    Route::post("appointments/index", [AppointmentController::class, "index"]);
    Route::resource("appointments", AppointmentController::class);

    Route::group(['middleware' => ['permission:calendar']], function () {
        Route::get("/medical-records/calendar", [MedicalRecordController::class, "calendar"]);
        Route::put("/medical-records/update_aux/{id}", [MedicalRecordController::class, "update_aux"]);
    });

    Route::group(['middleware' => ['permission:show_medical_records']], function () {
        Route::post("/medical-records/pet", [MedicalRecordController::class, "index"]);
    });

    Route::post("vaccinations/index", [VaccinationController::class, "index"]);
    Route::resource("vaccinations", VaccinationController::class);

    Route::post("surgeries/index", [SurgerieController::class, "index"]);
    Route::resource("surgeries", SurgerieController::class);

    Route::group(['middleware' => ['permission:show_payment|edit_payment']], function () {
        Route::post("payments/index", [PaymentController::class, "index"]);
        Route::resource("payments", PaymentController::class);
    });

    Route::group(['middleware' => ['permission:show_report_grafics']], function () {
        Route::post("kpi_report_general", [KpiController::class, "kpi_report_general"]);
        Route::post("kpi_veterinarie_net_income", [KpiController::class, "kpi_veterinarie_net_income"]);
        Route::post("kpi_veterinarie_most_asigned", [KpiController::class, "kpi_veterinarie_most_asigned"]);
        Route::post("kpi_total_bruto", [KpiController::class, "kpi_total_bruto"]);
        Route::post("kpi_report_for_servicies", [KpiController::class, "kpi_report_for_servicies"]);
        Route::post("kpi_pets_most_payments", [KpiController::class, "kpi_pets_most_payments"]);
        Route::post("kpi_payments_x_day_month", [KpiController::class, "kpi_payments_x_day_month"]);
        Route::post("kpi_payments_x_month_of_year", [KpiController::class, "kpi_payments_x_month_of_year"]);
    });


});
Route::get("appointment-excel", [AppointmentController::class, "downloadExcel"]);
Route::get("vaccination-excel", [VaccinationController::class, "downloadExcel"]);
Route::get("surgeries-excel", [SurgerieController::class, "downloadExcel"]);
Route::get("payments-excel", [PaymentController::class, "downloadExcel"]);

###################### DESDE AQUI SON RUTAS PARA EL APLICATIVO ##########################
// RUTA DE LOGIN DESDE EL APLICATIVO MOVIL
Route::post('app/owner-login-app', [OwnerController::class, 'loginOwnerApp']);
Route::post('app/user-login-app', [AuthController::class, 'loginUserApp']);

// RUTAS PROTEGIAS CON SANCTUM (Una vez iniciando sesion)
Route::prefix('app')->middleware(['auth:sanctum'])->group(function () {

    // DUEÑOS Y SUS MASCOTAS Vista del Admin
    Route::prefix('admin/owners')->group(function () {
        Route::get('/', [OwnerController::class, 'index']); // Obtener todos los dueños
        Route::post('/', [OwnerController::class, 'store']); // Crear un dueño
        Route::put('/{id}', [OwnerController::class, 'update']); // Actualizar un dueño
        Route::delete('/{id}', [OwnerController::class, 'destroy']); // Eliminar (soft delete)
        Route::get('/{id}/pets', [OwnerController::class, 'getOwnerPets']); // Obtener mascotas de un dueño
        Route::put('/toggle-active/{id}', [OwnerController::class, 'toggleActive']); //Activar y desactivar "ELIMINAR deleted_at
        Route::get('/search', [OwnerController::class, "searchOwners"]);//Buscar Mediante Nombres y Apellidos
        Route::get('/{id}', [OwnerController::class, 'show']); // Obtener un dueño por ID
    });

    // Mascotas Vista del Admin
    Route::prefix('admin/pets')->group(function () {
        Route::get('/', [PetsController::class, 'indexApp']); // Obtener las mascotas y su dueño
        Route::get('/{id}', [PetsController::class, 'getPetById']); // Obtener una mascota
        Route::put('/{id}', [PetsController::class, 'updateApp']); // Actualizar datos de la mascota
        Route::delete('/{id}', [PetsController::class, 'destroyApp']); // Eliminar (soft delete)
        Route::put('/toggle-active/{id}',[PetsController::class, 'toggleActive']); /// Activar y Desactivar mascotas
        Route::get('/search', [PetsController::class, 'search']);//Buscar mendiante Mascota y Dueño
    });

    //  MASCOTAS Y SU HISTORIAL MÉDICO
    Route::prefix('pets')->group(function () {
        Route::get('/{id}', [PetsController::class, 'getPetById']); // Obtener una mascota
        Route::get('/{id}/appointments', [AppointmentController::class, 'getAppointmentsByPetId']); // Citas médicas
        Route::get('/{id}/surgeries', [SurgerieController::class, 'getSurgeriesByPetId']); // Cirugías
        Route::get('/{id}/vaccinations', [VaccinationController::class, 'getVaccinationsByPetId']); // Vacunaciones
    });

    // DASHBOARD SUPER ADMIN - GESTION DE USUARIOS
    Route::prefix('users')->group(function () {
        Route::get('/', [AuthController::class, 'getUsersForSuperAdmin']); // Obtener usuarios
        Route::put('/{id}', [AuthController::class, 'updateUser']); // Actualizar usuario
        Route::delete('/{id}', [AuthController::class, 'destroyUser']); // Eliminar usuario (soft delete)
    });

    // METRICAS DEL DASHBOARD
    Route::get('/dashboard/metrics', [DashboardAppController::class, 'getDashboardAppMetrics']); // Obtener métricas

});


Route::get('/searching', [OwnerController::class, "searchOwners"]);//Buscar Mediante Nombres y Apellidos
