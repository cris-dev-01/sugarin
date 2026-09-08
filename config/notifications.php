<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Días sin seguimiento
    |--------------------------------------------------------------------------
    |
    | Cantidad de días sin un registro de glucosa a partir de los cuales un
    | paciente se considera "sin seguimiento" para efectos de la alerta
    | estratégica (ver App\Console\Commands\NotifyPatientsWithoutFollowUpCommand).
    |
    */

    'follow_up_days' => env('NOTIFICATIONS_FOLLOW_UP_DAYS', 3),

    /*
    |--------------------------------------------------------------------------
    | Ventana de lecturas alteradas
    |--------------------------------------------------------------------------
    |
    | Cantidad de horas hacia atrás en las que se busca al menos una lectura
    | de glucosa con status "Elevado" o "Bajo" para disparar la alerta de
    | lecturas alteradas (ver App\Console\Commands\NotifyPatientsWithAbnormalReadingsCommand).
    |
    */

    'abnormal_reading_window_hours' => env('NOTIFICATIONS_ABNORMAL_READING_WINDOW_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Cantidad de notificaciones no leídas a compartir
    |--------------------------------------------------------------------------
    |
    | Número máximo de notificaciones no leídas que se exponen al frontend
    | vía la prop compartida de Inertia.
    |
    */

    'shared_unread_limit' => env('NOTIFICATIONS_SHARED_UNREAD_LIMIT', 10),

];
