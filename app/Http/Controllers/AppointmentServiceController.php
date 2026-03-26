<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentServiceRequest;
use App\Http\Requests\UpdateAppointmentServiceRequest;
use App\Models\AppointmentService;

class AppointmentServiceController extends Controller
{
  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreAppointmentServiceRequest $request)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateAppointmentServiceRequest $request, AppointmentService $appointmentService)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(AppointmentService $appointmentService)
  {
    //
  }
}
