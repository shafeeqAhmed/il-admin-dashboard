<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

//use DB;

class RescheduledAppointment extends Model {

    use \BinaryCabin\LaravelUUID\Traits\HasUUID;

    protected $table = 'rescheduled_appointments';
    protected $primaryKey = 'id';
    protected $uuidFieldName = 'rescheduled_appointment_uuid';
    public $timestamps = true;
    protected $fillable = [
        'rescheduled_appointment_uuid',
        'appointment_id',
        'rescheduled_by_id',
        'rescheduled_by_type',
        'previous_from_time',
        'previous_to_time',
        'previous_appointment_date',
        'previous_status',
        'is_archive',
    ];

    public function Appointment() {
        return $this->belongsTo('\App\Appointment', 'appointment_uuid', 'appointment_uuid');
    }

    public function Customer() {
        return $this->belongsTo('\App\Customer', 'rescheduled_by_id', 'id');
    }

    public function Freelancer() {
        return $this->belongsTo('\App\Freelancer', 'rescheduled_by_id', 'id');
    }

    protected function createData($data) {
        $result = RescheduledAppointment::create($data);
        return ($result) ? $result->toArray() : [];
    }

}
