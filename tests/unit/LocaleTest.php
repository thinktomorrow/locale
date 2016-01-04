<?php

namespace Thinktomorrow\Locale\Tests;

use TestCase;
use Thinktomorrow\Locale\Locale;

class LocaleTest extends TestCase
{
    private $locale;

   public function setUp()
   {
       parent::setUp();

       $this->locale = new Locale(app()->make('request'),[
           'available_locales' => ['nl','fr'],
           'fallback_locale' => 'nl',
           'naked_locale' => null,
       ]);
   }

    /** @test */
    public function it_can_be_called()
    {
        $locale = app()->make(Locale::class);

        $this->assertInstanceOf(Locale::class,$locale);
    }

    /** @test */
    public function it_gets_available_locale()
    {
        $this->assertEquals('fr',$this->locale->getOrFail('fr'));
    }

    /** @test */
    public function it_gets_fallback_locale_if_locale_is_not_available()
    {
        $this->locale->set();

        $this->assertEquals('nl',$this->locale->get('foo'));
    }

    /** @test */
    public function on_strict_mode_it_returns_false_if_locale_is_not_available()
    {
        $this->assertFalse($this->locale->getOrFail('foo'));
        $this->assertFalse($this->locale->getOrFail('lu'));
    }

    /** @test */
    public function it_sets_the_locale_if_found_in_cookie()
    {
        $request = \Mockery::mock('Illuminate\Http\Request');

        $request->shouldReceive('cookie')->twice()->withArgs(['locale'])->andReturn('foobar');
        $request->shouldReceive('get')->once();
        $request->shouldReceive('getHost')->twice();
        $request->shouldReceive('segment')->once();

        $locale = new Locale($request,[
            'available_locales' => ['nl','fr','foobar'],
            'fallback_locale' => 'nl',
            'naked_locale' => null,
        ]);

        $locale->set();

        $this->assertEquals('foobar',$locale->get());
    }

    /** @test */
    public function it_sets_the_locale_if_found_as_url_segment()
    {
        $request = \Mockery::mock('Illuminate\Http\Request');

        $request->shouldReceive('cookie')->once()->withArgs(['locale'])->andReturn(false);
        $request->shouldReceive('get')->once();
        $request->shouldReceive('getHost')->times(4);
        $request->shouldReceive('segment')->twice()->andReturn('foobar');

        $locale = new Locale($request,[
            'available_locales' => ['nl','fr','foobar'],
            'fallback_locale' => 'nl',
            'naked_locale' => 'fr',
        ]);

        $locale->set();

        $this->assertEquals('foobar',$locale->get());
    }

    /** @test */
    public function it_sets_the_locale_if_naked_locale_is_set()
    {
        $request = \Mockery::mock('Illuminate\Http\Request');

        $request->shouldReceive('cookie')->once()->withArgs(['locale'])->andReturn(false);
        $request->shouldReceive('get')->once();
        $request->shouldReceive('getHost')->times(4);
        $request->shouldReceive('segment')->twice()->andReturn(null);

        $locale = new Locale($request,[
            'available_locales' => ['nl','fr','foobar'],
            'fallback_locale' => 'nl',
            'naked_locale' => 'fr',
        ]);

        $locale->set();

        $this->assertEquals('fr',$locale->get());
    }

    /** @test */
    public function passing_query_param_has_priority()
    {
        $request = \Mockery::mock('Illuminate\Http\Request');

        $request->shouldReceive('cookie')->twice()->withArgs(['locale'])->andReturn('fr');
        $request->shouldReceive('get')->twice()->andReturn('foobar');
        $request->shouldReceive('getHost')->times(4);
        $request->shouldReceive('segment')->twice()->andReturn('fr');

        $locale = new Locale($request,[
            'available_locales' => ['nl','fr','foobar'],
            'fallback_locale' => 'nl',
            'naked_locale' => 'fr',
        ]);

        $locale->set();

        $this->assertEquals('foobar',$locale->get());
    }

    /** @test */
    public function passing_locale_to_method_has_priority()
    {
        $request = \Mockery::mock('Illuminate\Http\Request');

        $request->shouldReceive('cookie')->never();
        $request->shouldReceive('get')->never();
        $request->shouldReceive('getHost')->never();
        $request->shouldReceive('segment')->never();

        $locale = new Locale($request,[
            'available_locales' => ['nl','fr','foobar','fooz'],
            'fallback_locale' => 'nl',
            'naked_locale' => 'fr',
        ]);

        $locale->set('fooz');

        $this->assertEquals('fooz',$locale->get());
    }



}
