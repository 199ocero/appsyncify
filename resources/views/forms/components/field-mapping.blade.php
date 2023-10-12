<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @for ($i = 0; $i < $mappedItems['COUNT']; $i++)
        <div class="field-mapping-item">
            <div class="field-mapping-app">
                <img class="field-mapping-app-logo" src="{{ asset($mappedItems['FIRST_APP_LOGO']) }}"
                    alt="Salesforce Logo">
                <x-filament::input.wrapper class="py-2 px-4 w-full">
                    <span class="text-sm">{{ $mappedItems['FIRST_APP'][$i] }}</span>
                </x-filament::input.wrapper>
            </div>
            <x-filament::icon-button icon="heroicon-o-chevron-double-right" size="sm" disabled="true" />
            <div class="field-mapping-app">
                <x-filament::input.wrapper class="py-2 px-4 w-full">
                    <span class="text-sm">{{ $mappedItems['SECOND_APP'][$i] }}</span>
                </x-filament::input.wrapper>
                <img class="field-mapping-app-logo" src="{{ asset($mappedItems['SECOND_APP_LOGO']) }}"
                    alt="Mailchimp Logo">
            </div>
        </div>
    @endfor
</x-dynamic-component>
