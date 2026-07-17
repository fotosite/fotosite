<?php

return [
    /*
     * Gültigkeitsdauer des "Gerät als sicher merken"-Features in Tagen.
     * Steuert sowohl die Cookie-Lebensdauer als auch die trusted_device.
     * expires_at-Berechnung sowie den angezeigten Text im Login-Formular.
     */
    'days' => (int) env('TRUSTED_DEVICE_DAYS', 7),
];
