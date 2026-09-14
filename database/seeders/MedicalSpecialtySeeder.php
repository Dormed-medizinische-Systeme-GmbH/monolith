<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Crm\Models\MedicalSpecialty;
use Illuminate\Database\Seeder;

/**
 * Fachrichtungen (D-019, erweitert durch D-133).
 *
 * KEIN Demo-Seed: das Website-Kontaktformular zieht seine Auswahl aus dieser
 * Tabelle. Ohne sie hat das Formular kein Fachgebiet-Feld.
 *
 * Die ersten 21 stammen verbatim aus dem Altsystem, die letzten fuenf aus der
 * Auswahlliste des Kontaktformulars — vorher zwei konkurrierende Listen, jetzt
 * eine (D-133).
 *
 * > Die Benennung ist dadurch gemischt: der Altsystem-Seed nennt Personen im
 * > Plural („Orthopäden"), die neuen Werte nennen Fachgebiete („Gynäkologie").
 * > Eine Vereinheitlichung beruehrt Bestandsdaten und ist eine eigene
 * > Entscheidung (CORE.md, offener Punkt).
 */
final class MedicalSpecialtySeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private const FROM_LEGACY = [
        'Pulmologen', 'Institution', 'Hdin', 'Orthopäden', 'Dermatologen',
        'Veterinäre', 'Radiologen', 'Chirurgen', 'Kardiologen', 'HNO',
        'Sportmedizin', 'USVE', 'Bahnarzt', 'Rheumatologen', 'Hebammen',
        'Werksarzt', 'Unfallchirurgie', 'Sanitätshaus', 'Heilpraktiker',
        'Phlebologie', 'Neurologen',
    ];

    /**
     * Aus der Auswahlliste des Kontaktformulars (D-133). „Sonstiges" ist eine
     * Ergaenzung: der D-019-Seed hatte keinen Auffangwert, das Formular
     * braucht aber einen.
     *
     * @var list<string>
     */
    private const FROM_CONTACT_FORM = [
        'Allgemeinmedizin / Hausarzt', 'Innere Medizin', 'Gynäkologie',
        'Urologie', 'Sonstiges',
    ];

    public function run(): void
    {
        foreach ([...self::FROM_LEGACY, ...self::FROM_CONTACT_FORM] as $name) {
            MedicalSpecialty::query()->updateOrCreate(
                ['name' => $name],
                ['is_active' => true],
            );
        }
    }
}
