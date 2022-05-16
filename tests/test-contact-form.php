<?php
namespace Recras;

class ContactFormTest extends WordPressUnitTestCase
{
	function testShortcodeWithoutID(): void
	{
        $content = $this->createPostAndGetContent('[recras-contact]');
        $this->assertEquals('Error: no ID set' . "\n", $content, 'Not setting ID should fail');
	}

	function testInvalidIDinShortcode(): void
	{
	    $content = $this->createPostAndGetContent('[recras-contact id=foobar]');
        $this->assertEquals('Error: ID is not a number' . "\n", $content, 'Non-numeric ID should fail');
	}

    function testRegularContactForm(): void
    {
        $content = $this->createPostAndGetContent('[recras-contact id=3]');
        $this->assertNotFalse(strpos($content, '<h2'), 'Regular contact form should include a title');
        $this->assertNotFalse(strpos($content, '<dl'), 'Regular contact form should be a definition list');
        $this->assertNotFalse(strpos($content, '<dt'), 'Regular contact form should include definition terms');
        $this->assertNotFalse(strpos($content, '<dd'), 'Regular contact form should include definitions');
        $this->assertNotFalse(strpos($content, '<label'), 'Regular contact form should show labels');
        $this->assertNotFalse(strpos($content, 'placeholder='), 'Regular contact form should show placeholders');
    }

    function testContactformNoTitle(): void
    {
        $content = $this->createPostAndGetContent('[recras-contact id=3 showtitle=false]');
        $this->assertFalse(strpos($content, '<h2'), 'Setting showtitle to false should not generate a title');
    }

    function testContactformNoLabels(): void
    {
        $content = $this->createPostAndGetContent('[recras-contact id=3 showlabels="no"]');
        $this->assertFalse(strpos($content, '<label'), 'Setting showlabels to false should not generate labels');
    }

    function testContactformNoPlaceholders(): void
    {
        $content = $this->createPostAndGetContent('[recras-contact id=3 showplaceholders=0]');
        $this->assertFalse(strpos($content, 'placeholder='), 'Setting showplaceholders to false should not generate placeholders');
    }

    function testContactformAsOrderedList(): void
    {
        $content = $this->createPostAndGetContent('[recras-contact id=3 element=ol]');
        $this->assertFalse(strpos($content, '<dl'), 'Contact form with element set to ol should not be a definition list');
        $this->assertNotFalse(strpos($content, '<ol'), 'Contact form with element set to ol should be an ordered list');
        $this->assertNotFalse(strpos($content, '<li'), 'Contact form with element set to ol should have list items');
    }

    function testContactformAsTable(): void
    {
        $content = $this->createPostAndGetContent('[recras-contact id=3 element=table]');
        $this->assertFalse(strpos($content, '<dl'), 'Contact form with element set to ol should not be a definition list');
        $this->assertNotFalse(strpos($content, '<table'), 'Contact form with element set to table should be a table');
        $this->assertNotFalse(strpos($content, '<tr'), 'Contact form with element set to table should have at least one row');
        $this->assertNotFalse(strpos($content, '<td'), 'Contact form with element set to table should have at least one cell');
    }

    function testSubmitDifferentText(): void
    {
        $content = $this->createPostAndGetContent('[recras-contact id=3 submittext="Ni"]');
        $this->assertNotFalse(strpos($content, '<input type="submit" value="Ni">'), 'Changing submit button text should work');
    }

    function testGetForms(): void
    {
        $plugin = new ContactForm();
        $forms = $plugin->getForms('demo');
        $this->assertGreaterThan(0, count($forms), 'getForms should return a non-empty array');
    }
}
