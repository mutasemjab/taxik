  <header>
        <nav class="container">
            <div class="logo">Taksi K</div>
            <ul class="nav-links">
                <li><a href="#home">{{ __('front.home') }}</a></li>
                <li><a href="#features">{{ __('front.features') }}</a></li>
                <li><a href="#apps">{{ __('front.apps') }}</a></li>
                <li><a href="#safety">{{ __('front.safety') }}</a></li>
                <li><a href="#contact">{{ __('front.contact') }}</a></li>
                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                    <a class="nav-link"  hreflang="{{ $localeCode }}" href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                        {{ $properties['native'] }}
                    </a>
                @endforeach
            </ul>
        </nav>
    </header>