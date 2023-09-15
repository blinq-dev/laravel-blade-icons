@if($lazy)
    <svg data-lazy="{{ $id }}" {{ $attributes }}></svg>
@else
    @php
        $missing = false;
        $missingMessage = '';
        $name = 'missing';
        $pack = 'fallback';
        $variant = null;
        $icon = null;

        # <x-icon class='w-6 h-6' id='account_box@material/default' />
        # <x-icon class='w-6 h-6' id='icon@pack/variant' />
        # validate id with regex
        if (preg_match('/^[a-z0-9_\-]+@[a-z0-9_\-]+\/[a-z0-9_\-]+$/i', $id)) {
            $name = str($id)->before('@');
            $pack = str($id)->after('@')->before('/');
            $variant = str($id)->after('/');

            $iconPack = \Blinq\Icons\IconPack::get($pack);

            if ($iconPack) {
                $icon = $iconPack->getIcon($name, $variant, $attributes);

            } else {
                $missing = true;
                $missingMessage = 'Icon pack not found: ' . $pack;
            }

            if (!$icon) {
                $missing = true;
                $missingMessage = 'Icon not found: ' . $id;
            }
        } else {
            $missing = true;
            $missingMessage = 'Format is invalid: ' . $id . ' (expected: icon@pack/variant)';
        }

        if ($missing) {
            $iconPack = \Blinq\Icons\IconPack::get('fallback');
            $icon = $iconPack->getIcon('missing', null, $attributes);
            $attributes = $attributes->merge([
                'title' => $missingMessage,
            ]);
        }

    @endphp
    {{-- @dump($id, $missingMessage) --}}
    {{-- Replace: --}}
    {{-- <x-icon (class=['|"][^'|"]*['|"] )?pack=['|"]([^'|"]*)['|"] name=['|"]([^'|"]*)['|"] --}}
    {{-- <x-icon $1id="$3@$2" --}}

    <!-- icon: {{ $pack }} {{ $name }} -->
    {!! $icon !!}
@endif