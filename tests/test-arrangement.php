<?php
namespace Recras;

class ArrangementTest extends WordPressUnitTestCase
{
    function testShortcodeWithoutID()
    {
        $content = $this->createPostAndGetContent('[recras-package]');
        $this->assertEquals('Error: no ID set' . "\n", $content, 'Not setting ID should fail');
    }

    function testInvalidIDinShortcode()
    {
        $content = $this->createPostAndGetContent('[recras-package id=foobar]');
        $this->assertEquals('Error: ID is not a number' . "\n", $content, 'Non-numeric ID should fail');
    }

    function testShortcodeWithoutShow()
    {
        $content = $this->createPostAndGetContent('[recras-package id=7]');
        $this->assertEquals('<span class="recras-title">Actieve Familiedag</span>' . "\n", $content, 'Not setting "show" option should default to title');
    }

    function testShortcodeWithInvalidShow()
    {
        $content = $this->createPostAndGetContent('[recras-package id=7 show=invalid]');
        $this->assertEquals('<span class="recras-title">Actieve Familiedag</span>' . "\n", $content, 'Invalid "show" option should default to title');
    }

    function testShortcodeShowTitle()
    {
        $content = $this->createPostAndGetContent('[recras-package id=7 show=title]');
        $this->assertEquals('<span class="recras-title">Actieve Familiedag</span>' . "\n", $content, 'Should show title');
    }

    function testShortcodeDescription()
    {
        $content = $this->createPostAndGetContent('[recras-package id=7 show=description]');
        $this->assertTrue(strpos($content, 'Uitgebreide omschrijving van dit arrangement') !== false, 'Should show description');
    }

    function testShortcodeDuration()
    {
        $content = $this->createPostAndGetContent('[recras-package id=7 show=duration]');
        $this->assertEquals('<span class="recras-duration">4:15</span>' . "\n", $content, 'Should show duration');
    }

    function testShortcodeImageTag()
    {
        $content = $this->createPostAndGetContent('[recras-package id=7 show=image_tag]');
        $this->assertEquals('<img src="https://demo.recras.nl/api2/arrangementen/7/afbeelding" alt="Actieve Familiedag">' . "\n", $content, 'Should return image tag');
    }

    function testShortcodeImageUrl()
    {
        $content = $this->createPostAndGetContent('[recras-package id=7 show=image_url]');
        $this->assertEquals('/api2/arrangementen/7/afbeelding' . "\n", $content, 'Should return image URL');
    }

    function testShortcodeImageInTag()
    {
        $content = $this->createPostAndGetContent('<img src="[recras-package id=7 show=image_url]">');
        $this->assertEquals('<p><img src="/api2/arrangementen/7/afbeelding"></p>' . "\n", $content, 'Should return image URL');
    }

    function testShortcodeLocation()
    {
        $content = $this->createPostAndGetContent('[recras-package id=7 show=location]');
        $this->assertEquals('<span class="recras-location">No location specified</span>' . "\n", $content, 'Should show location');
    }

    function testShortcodeShowPersons()
    {
        $content = $this->createPostAndGetContent('[recras-package id=7 show=persons]');
        $this->assertEquals('<span class="recras-persons">10</span>' . "\n", $content, 'Should show number of persons');
    }

    function testShortcodeShowPrices()
    {
        $content = $this->createPostAndGetContent('[recras-package id=7 show=price_total_excl_vat]');
        $this->assertEquals('<span class="recras-price">€ 385.66</span>' . "\n", $content, 'Should show total price excl. vat');

        $content = $this->createPostAndGetContent('[recras-package id=7 show=price_total_incl_vat]');
        $this->assertEquals('<span class="recras-price">€ 415.00</span>' . "\n", $content, 'Should show total price incl. vat');

        $content = $this->createPostAndGetContent('[recras-package id=7 show=price_pp_excl_vat]');
        $this->assertEquals('<span class="recras-price">€ 38.57</span>' . "\n", $content, 'Should show price per person excl. vat');

        $content = $this->createPostAndGetContent('[recras-package id=7 show=price_pp_incl_vat]');
        $this->assertEquals('<span class="recras-price">€ 41.50</span>' . "\n", $content, 'Should show price per person incl. vat');
    }

    function testSingleDayProgramme()
    {
        $content = $this->createPostAndGetContent('[recras-package id=7 show=programme]');
        $this->assertNotFalse(strpos($content, '<table'), 'Should return an HTML table');
        $this->assertNotFalse(strpos($content, '<thead'), 'Should contain a table header');
        $this->assertEquals(0, substr_count($content, '<tr class="recras-new-day'), 'Should stay on one day');
    }

    function testProgrammeWithTimeOffset()
    {
        $content = $this->createPostAndGetContent('[recras-package id=7 starttime="14:00" show=programme]');
        $this->assertNotFalse(strpos($content, '<td>14:00<td>16:00'), 'Should move start and end times');
        $this->assertNotFalse(strpos($content, '<td>16:00<td>18:15'), 'Should move start and end times for all lines');
    }

    function testMultiDayProgramme()
    {
        $content = $this->createPostAndGetContent('[recras-package id=5 show=programme]');
        $this->assertEquals(2, substr_count($content, '<tr class="recras-new-day'), 'Should span two days');
    }

    function testShortcodeProgrammeWithoutHeader()
    {
        $content = $this->createPostAndGetContent('[recras-package id=7 show=programme showheader=false]');
        $this->assertNotFalse(strpos($content, '<table'), 'Should return an HTML table');
        $this->assertFalse(strpos($content, '<thead'), 'Should not contain a table header');
    }

    function testGetArrangements()
    {
        $plugin = new Arrangement();
        $arrangements = $plugin->getPackages('demo');
        $this->assertGreaterThan(0, count($arrangements), 'getArrangements should return a non-empty array');
    }

    function testGetOnlineArrangements()
    {
        $plugin = new Arrangement();
        $packages = $plugin->getPackages('demo', true, false);
        $this->assertTrue(is_array($packages));
        $packagesOnline = array_filter($packages, function($p) {
            return $p->mag_online;
        });
        $this->assertEquals($packages, $packagesOnline, 'All packages should be bookable online');
    }

    function testGetFormArrangementsInvalidForm()
    {
        $plugin = new Arrangement();
        $arrangements = $plugin->getPackagesForContactForm('demo', 1337);
        $this->assertTrue(is_string($arrangements), 'Non-existing contact form should return an error message');
    }

    function testGetFormArrangements()
    {
        $plugin = new Arrangement();
        $arrangements = $plugin->getPackagesForContactForm('demo', 1);
        $this->assertGreaterThan(0, count($arrangements), 'Existing contact form should return a non-empty array');
    }

    function testChangeDecimal()
    {
        update_option('recras_decimal', ',');
        $content = $this->createPostAndGetContent('[recras-package id=7 show=price_total_excl_vat]');
        $this->assertEquals('<span class="recras-price">€ 385,66</span>' . "\n", $content, 'Should respect decimal setting');
        update_option('recras_decimal', '.');
    }

    function testChangeCurrency()
    {
        update_option('recras_currency', '¥');
        $content = $this->createPostAndGetContent('[recras-package id=7 show=price_total_excl_vat]');
        $this->assertEquals('<span class="recras-price">¥ 385.66</span>' . "\n", $content, 'Should respect currency setting');
        update_option('recras_currency', '€');
    }
}
