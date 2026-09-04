<?php

return [
    /*
     * E-Mail-Adresse, an die sich ein deaktivierter Galerist dringend
     * wenden soll (erscheint im Fließtext der Deaktivierungs-Mail).
     */
    'contact_email' => env('MAND_DEACTIVATION_CONTACT_EMAIL'),

    /*
     * Karenzzeit in Tagen zwischen Deaktivierung und der Möglichkeit,
     * das Galeristenkonto endgültig zu löschen.
     */
    'grace_days' => (int) env('MAND_DELETE_GRACE_DAYS', 7),
];
