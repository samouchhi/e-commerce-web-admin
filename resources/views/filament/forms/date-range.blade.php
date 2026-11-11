<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            ranges: @js($ranges), start: '', end: '', month: '', allTime: false, busy: false, error: '', expanded: false, panelLeft: 0, panelWidth: 704, panelHeight: 620,
            iso(date) { return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}` },
            date(value) { return new Date(value + 'T12:00:00') },
            months: @js(app()->getLocale() === 'km' ? ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'] : null),
            format(value) { if (!value) return @js(__('Select end date')); const d = this.date(value); return this.months ? `${d.getDate()} ${this.months[d.getMonth()]} ${d.getFullYear()}` : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) },
            open() {
                this.start = $wire.filters.start_date; this.end = $wire.filters.end_date;
                this.allTime = $wire.filters.date_range === 'all_time';
                this.month = this.start.slice(0, 7); this.error = ''; this.position(); this.expanded = true;
            },
            position() {
                const rect = this.$refs.trigger.getBoundingClientRect();
                const width = document.documentElement.clientWidth;
                this.panelWidth = Math.min(704, width - 32);
                this.panelLeft = Math.min(0, width - 16 - rect.left - this.panelWidth);
                this.panelHeight = Math.max(240, Math.min(620, window.innerHeight - rect.bottom - 24));
            },
            close(restoreFocus = true) { this.expanded = false; if (restoreFocus) this.$refs.trigger.focus() },
            shift(amount) { const d = this.date(this.month + '-01'); d.setMonth(d.getMonth() + amount); this.month = this.iso(d).slice(0, 7) },
            title(offset) { const d = this.date(this.month + '-01'); d.setMonth(d.getMonth() + offset); return this.months ? `${this.months[d.getMonth()]} ${d.getFullYear()}` : d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) },
            days(offset) {
                const first = this.date(this.month + '-01'); first.setMonth(first.getMonth() + offset);
                const count = new Date(first.getFullYear(), first.getMonth() + 1, 0).getDate();
                return Array.from({ length: first.getDay() + count }, (_, i) => i < first.getDay() ? null : this.iso(new Date(first.getFullYear(), first.getMonth(), i - first.getDay() + 1)));
            },
            pick(day) {
                this.allTime = false;
                if (!this.start || this.end) { this.start = day; this.end = ''; }
                else { [this.start, this.end] = [this.start, day].sort(); }
            },
            preset(key) { this.allTime = key === 'all_time'; [this.start, this.end] = this.ranges[key]; this.month = this.start.slice(0, 7) },
            moveFocus(event, day) {
                const amount = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 }[event.key];
                if (!amount) return;
                event.preventDefault(); const d = this.date(day); d.setDate(d.getDate() + amount);
                const next = this.iso(d); this.month = next.slice(0, 7);
                this.$nextTick(() => this.$refs.dialog.querySelector(`[data-day='${next}']`)?.focus());
            },
            async apply() {
                this.busy = true; this.error = '';
                try { await $wire.applyDateRange(this.start, this.end, this.allTime); this.close() }
                catch (e) { this.error = @js(__('Could not apply this range. Please try again.')) }
                finally { this.busy = false }
            }
        }"
        class="dashboard-range"
        @keydown.escape.prevent.stop="if (expanded) close()"
        @resize.window="if (expanded) position()"
        @focusout="$nextTick(() => { if (expanded && !busy && !$el.contains(document.activeElement)) close(false) })"
    >
        <button type="button" id="{{ $getId() }}" x-ref="trigger" class="dashboard-range-trigger" aria-haspopup="dialog" :aria-expanded="expanded" aria-controls="dashboard-range-panel" @click="expanded ? close() : open()">
            <x-filament::icon icon="heroicon-o-calendar-days" class="size-5 shrink-0" />
            <span x-text="$wire.filters.date_range === 'all_time' ? @js(__('All Time')) : format($wire.filters.start_date) + ' – ' + format($wire.filters.end_date)"></span>
            <x-filament::icon icon="heroicon-o-chevron-down" class="ms-auto size-4 shrink-0" />
        </button>

        <div x-ref="dialog" id="dashboard-range-panel" x-cloak x-show="expanded" role="dialog" class="dashboard-range-dialog" aria-labelledby="dashboard-range-title"
            :style="{ left: panelLeft + 'px', width: panelWidth + 'px', maxHeight: panelHeight + 'px' }"
            @click.outside="if (expanded && !$refs.trigger.contains($event.target)) close(false)">
            <div class="dashboard-range-header">
                <div>
                    <h2 id="dashboard-range-title">{{ __('Choose date range') }}</h2>
                    <p>{{ __('Select a start and end date, or use a preset.') }}</p>
                </div>
                <button type="button" @click="close()" aria-label="{{ __('Close date picker') }}" class="dashboard-range-nav"><x-filament::icon icon="heroicon-o-x-mark" class="size-5" /></button>
            </div>
            <div class="dashboard-range-body">
                <div class="dashboard-range-presets" aria-label="{{ __('Date presets') }}">
                    <button type="button" @click="preset('all_time')" :aria-pressed="allTime">{{ __('All Time') }}</button>
                    @foreach (['today' => 'Today', 'yesterday' => 'Yesterday', 'last_7_days' => 'Last 7 days', 'last_30_days' => 'Last 30 days', 'this_month' => 'This month', 'last_month' => 'Last month'] as $key => $label)
                        <button type="button" @click="preset(@js($key))" :aria-pressed="!allTime && start === ranges[@js($key)][0] && end === ranges[@js($key)][1]">{{ __($label) }}</button>
                    @endforeach
                </div>
                <div class="dashboard-range-calendars">
                    <div class="dashboard-range-navigation">
                        <button type="button" class="dashboard-range-nav" @click="shift(-1)" aria-label="{{ __('Previous month') }}"><x-filament::icon icon="heroicon-o-chevron-left" class="size-4" /></button>
                        <label class="sr-only" for="dashboard-range-month">{{ __('Jump to month') }}</label>
                        <input id="dashboard-range-month" type="month" :value="month" @change="if ($event.target.value) month = $event.target.value; else $event.target.value = month" aria-label="{{ __('Jump to month') }}" />
                        <button type="button" class="dashboard-range-nav" @click="shift(1)" aria-label="{{ __('Next month') }}"><x-filament::icon icon="heroicon-o-chevron-right" class="size-4" /></button>
                    </div>
                    <div class="dashboard-range-months">
                        <template x-for="offset in [0, 1]" :key="offset">
                            <section class="dashboard-range-month" :aria-label="title(offset)">
                                <h3 x-text="title(offset)" aria-live="polite"></h3>
                                <div class="dashboard-range-grid">
                                    <template x-for="weekday in @js(app()->getLocale() === 'km' ? ['អាទិត្យ', 'ចន្ទ', 'អង្គារ', 'ពុធ', 'ព្រហស្បតិ៍', 'សុក្រ', 'សៅរ៍'] : ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'])"><span class="dashboard-range-weekday" x-text="weekday" aria-hidden="true"></span></template>
                                    <template x-for="(day, index) in days(offset)" :key="month + '-' + offset + '-' + index">
                                        <div>
                                            <button x-show="day" type="button" class="dashboard-range-day"
                                                :data-day="day" :aria-label="day ? format(day) : ''" :aria-pressed="day === start || day === end"
                                                :class="{ 'is-between': day && start && end && day > start && day < end, 'is-endpoint': day && (day === start || day === end) }"
                                                @click="pick(day)" @keydown="moveFocus($event, day)" x-text="day ? Number(day.slice(-2)) : ''"></button>
                                        </div>
                                    </template>
                                </div>
                            </section>
                        </template>
                    </div>
                </div>
            </div>
            <div class="dashboard-range-footer">
                <p aria-live="polite" x-text="allTime ? @js(__('All Time')) : format(start) + ' – ' + format(end)"></p>
                <div class="flex gap-2">
                    <x-filament::button color="gray" @click="close()">{{ __('Cancel') }}</x-filament::button>
                    <x-filament::button @click="apply()" x-bind:disabled="!start || !end || busy"><span x-text="busy ? @js(__('Applying…')) : @js(__('Apply range'))"></span></x-filament::button>
                </div>
                <p x-show="error" x-text="error" role="alert" class="text-danger-600"></p>
            </div>
        </div>
    </div>
</x-dynamic-component>
