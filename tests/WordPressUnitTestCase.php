<?php
namespace Recras;

class WordPressUnitTestCase extends \WP_UnitTestCase
{
    public function setUp(): void
    {
        global $recrasPlugin;

        $transient = $this->createMock(Transient::class);
        $transient->method('delete')->willReturn(0); // 0 indicates no error
        $transient->method('set')->willReturn(true);
        $transient->method('get')->will($this->returnCallback([&$this, 'transientGetCallback']));

        $recrasPlugin->transients = $transient;
    }

    public function createPostAndGetContent(string $content): string
    {
        $post = $this->factory->post->create_and_get([
            'post_content' => $content,
        ]);
        return apply_filters('the_content', $post->post_content);
    }

    private function contactForm(): \stdClass
    {
        return (object) [
            'id' => 17,
            'naam' => 'Standaard formulier',
            'Velden' => [
                (object) [
                    'id' => 42,
                    'naam' => 'Voornaam',
                    'verplicht' => true,
                    'field_identifier' => 'contactpersoon.voornaam',
                    'soort_invoer' => 'contactpersoon.voornaam',
                ],
            ],
            'Arrangementen' => [
                (object) [
                    'id' => 7,
                    'arrangement' => 'Familiedag',
                ]
            ],
        ];
    }

    private function package(): \stdClass
    {
        return (object) [
            'id' => 7,
            'weergavenaam' => 'Actieve Familiedag',
            'arrangement' => 'Familiedag',
            'uitgebreide_omschrijving' => 'Uitgebreide omschrijving van dit arrangement',
            'programma' => (object) [
                0 => (object) [
                    'begin' => 'PT0H0M0S',
                    'eind' => 'PT2H0M0S',
                    'omschrijving' => 'Eerste activiteit',
                ],
                2 => (object) [
                    'begin' => 'PT2H0M0S',
                    'eind' => 'PT4H15M0S',
                    'omschrijving' => 'Laatste activiteit',
                ],
            ],
            'image_filename' => '/api2/arrangementen/7/afbeelding',
            'aantal_personen' => 10,
            'mag_online' => true,
            'prijs_totaal_exc' => 385.6619366911,
            'prijs_totaal_inc' => 415,
            'prijs_pp_exc' => 38.56619366911,
            'prijs_pp_inc' => 41.5,
        ];
    }

    private function packageMultiday(): \stdClass
    {
        return (object) [
            'id' => 5,
            'weergavenaam' => 'Meerdaags programma',
            'arrangement' => 'Meerdaags programma',
            'uitgebreide_omschrijving' => 'Uitgebreide omschrijving van dit arrangement',
            'programma' => [
                (object) [
                    'begin' => 'PT0H0M0S',
                    'eind' => 'PT2H0M0S',
                    'omschrijving' => 'Eerste activiteit',
                ],
                (object) [
                    'begin' => 'P1DT0H0M0S',
                    'eind' => 'P1DT2H0M0S',
                    'omschrijving' => 'Activiteit op dag 2',
                ],
            ],
            'image_filename' => '/api2/arrangementen/5/afbeelding',
            'aantal_personen' => 4,
            'mag_online' => true,
            'prijs_totaal_exc' => 75.471698113,
            'prijs_totaal_inc' => 80,
            'prijs_pp_exc' => 18.867924528,
            'prijs_pp_inc' => 20,
        ];
    }

    private function products(): array
    {
        return [
            17 => (object) [
                'id' => 17,
                'weergavenaam' => 'Cola',
                'verkoop' => 2.5,
                'beschrijving_klant' => 'De echte cola',
                'uitgebreide_omschrijving' => '',
                'minimum_aantal' => 0,
                'duur' => null,
            ],
            42 => (object) [
                'id' => 42,
                'weergavenaam' => '2 uur klimmen',
                'verkoop' => 17.5,
                'beschrijving_klant' => 'Twee uur klimmen in ons bos',
                'uitgebreide_omschrijving' => 'Twee uur klimmen in ons klimbos, met de langste zipline van Nederland',
                'minimum_aantal' => 1,
                'duur' => 'PT2H00M00S',
            ],
        ];
    }

    /**
     * @return array|object[]|\stdClass|\stdClass[]|string
     */
    public function transientGetCallback(string $name)
    {
        if (preg_match('~^([a-z]+)_arrangements$~', $name)) {
            return [
                5 => $this->packageMultiday(),
                7 => $this->package(),
            ];
        }
        if (preg_match('~^([a-z]+)_arrangement_5$~', $name)) {
            return $this->packageMultiday();
        }
        if (preg_match('~^([a-z]+)_arrangement_([\d]+)$~', $name)) {
            return $this->package();
        }
        if (preg_match('~^([a-z]+)_contactform_1337_v2$~', $name)) {
            return 'Does not exist';
        }
        if (preg_match('~^([a-z]+)_contactform_([\d]+)_v2$~', $name)) {
            return $this->contactForm();
        }
        if (preg_match('~^([a-z]+)_contactforms$~', $name)) {
            return [
                $this->contactForm(),
            ];
        }
        if (preg_match('~^([a-z]+)_products_v2$~', $name)) {
            return $this->products();
        }
        if (preg_match('~^([a-z]+)_voucher_templates$~', $name)) {
            return [
                (object) [
                    'id' => 1,
                    'name' => 'Kadobon voor 2 keer klimmen',
                    'price' => 30,
                    'expire_days' => 365,
                    'contactform_id' => 1,
                ],
            ];
        }

        throw new Exception('Transient not supported');
    }
}
