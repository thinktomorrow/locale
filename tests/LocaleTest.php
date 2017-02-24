<?php

namespace Thinktomorrow\Locale\Tests;

use Thinktomorrow\Locale\Locale;

class LocaleTest extends TestCase
{
    private $locale;

    public function setUp()
    {
        parent::setUp();

        $this->locale = new Locale(app()->make('request'), [
           'available_locales' => ['nl', 'fr'],
           'fallback_locale'   => 'nl',
           'hidden_locale'     => null,
            'query_key'        => null,
       ]);
    }

    /** @test */
    public function it_can_be_called()
    {
        $locale = app()->make(Locale::class);

        $this->assertInstanceOf(Locale::class, $locale);
    }

    /** @test */
    public function it_can_define_custom_available_locales()
    {
        $this->locale->setAvailables(['foo']);

        $this->assertEquals('foo', $this->locale->get('foo'));
        $this->assertEquals('nl', $this->locale->get('fr')); // Non available locale will default to fallback locale
    }

    /** @test */
    public function it_gets_available_locale()
    {
        $this->locale->set('fr');
        $this->assertEquals('fr', $this->locale->get('fr'));
    }

    /** @test */
    public function it_gets_fallback_locale_if_locale_is_not_available()
    {
        $this->locale->set();

        $this->assertEquals('nl', $this->locale->get('foo'));
    }

    /** @test */
    public function it_sets_the_locale_if_found_in_cookie()
    {
        $request = \Mockery::mock('Illuminate\Http\Request');

        $request->shouldReceive('cookie')->twice()->withArgs(['locale'])->andReturn('foobar');
        $request->shouldReceive('get')->withArgs(['locale'])->times(1)->andReturn(null);
        $request->shouldReceive('segment')->once();

        $locale = new Locale($request, [
            'available_locales' => ['nl', 'fr', 'foobar'],
            'fallback_locale'   => 'nl',
            'hidden_locale'     => null,
            'query_key'         => 'locale',
        ]);

        $locale->set();

        $this->assertEquals('foobar', $locale->get());
    }

    /** @test */
    public function it_sets_the_locale_if_found_as_url_segment()
    {
        $request = \Mockery::mock('Illuminate\Http\Request');

        $request->shouldReceive('cookie')->once()->withArgs(['locale'])->andReturn(false);
        $request->shouldReceive('get')->withArgs(['locale'])->times(1)->andReturn(null);
        $request->shouldReceive('segment')->once()->andReturn('foobar');

        $locale = new Locale($request, [
            'available_locales' => ['nl', 'fr', 'foobar'],
            'fallback_locale'   => 'nl',
            'hidden_locale'     => 'fr',
            'query_key'         => 'locale',
        ]);

        $locale->set();

        $this->assertEquals('foobar', $locale->get());
    }

    /** @test */
    public function it_sets_the_locale_if_hidden_locale_is_set()
    {
        $request = \Mockery::mock('Illuminate\Http\Request');

        $request->shouldReceive('cookie')->once()->withArgs(['locale'])->andReturn(false);
        $request->shouldReceive('get')->withArgs(['locale'])->times(1)->andReturn(null);
        $request->shouldReceive('segment')->once()->andReturn(null);

        $locale = new Locale($request, [
            'available_locales' => ['nl', 'fr', 'foobar'],
            'fallback_locale'   => 'nl',
            'hidden_locale'     => 'fr',
            'query_key'         => 'locale',
        ]);

        $locale->set();

        $this->assertEquals('fr', $locale->get());
    }

    /** @test */
    public function passing_url_query_param_has_priority()
    {
        $request = \Mockery::mock('Illuminate\Http\Request');

        $request->shouldReceive('cookie')->twice()->withArgs(['locale'])->andReturn('fr');
        $request->shouldReceive('get')->withArgs(['locale'])->times(1)->andReturn('foobar');
        $request->shouldReceive('segment')->once()->andReturn('fr');

        $locale = new Locale($request, [
            'available_locales' => ['nl', 'fr', 'foobar'],
            'fallback_locale'   => 'nl',
            'hidden_locale'     => 'fr',
            'query_key'         => 'locale',
        ]);

        $locale->set();

        $this->assertEquals('foobar', $locale->get());
    }

    /** @test */
    public function passing_locale_to_method_has_top_priority()
    {
        $request = \Mockery::mock('Illuminate\Http\Request');

        $request->shouldReceive('cookie')->never();
        $request->shouldReceive('get')->never();
        $request->shouldReceive('getHost')->never();
        $request->shouldReceive('segment')->never();

        $locale = new Locale($request, [
            'available_locales' => ['nl', 'fr', 'foobar', 'fooz'],
            'fallback_locale'   => 'nl',
            'hidden_locale'     => 'fr',
            'query_key'         => 'locale',
        ]);

        $locale->set('fooz');

        $this->assertEquals('fooz', $locale->get());
    }
}
