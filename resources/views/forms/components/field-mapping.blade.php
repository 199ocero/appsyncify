<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="flex items-center justify-between gap-4">
        <x-filament::input.wrapper class="p-3 w-full">
            <span class="text-sm">{{ $field_1 }}</span>
        </x-filament::input.wrapper>
        <x-filament::icon-button icon="heroicon-o-chevron-double-right" size="sm" disabled="true" />
        <x-filament::input.wrapper class="p-3 w-full">
            <span class="text-sm">{{ $field_2 }}</span>
        </x-filament::input.wrapper>
    </div>
</x-dynamic-component>
