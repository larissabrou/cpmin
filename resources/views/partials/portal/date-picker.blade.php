@once
    @push('styles')
        <link href="{{ asset('theme/assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet">
        <style>
            .portal-date-wrap { position: relative; }
            .portal-date-wrap .portal-date-icon {
                position: absolute;
                right: 0.75rem;
                top: 50%;
                transform: translateY(-50%);
                pointer-events: none;
                color: var(--bs-secondary-color, #74788d);
                font-size: 1.1rem;
            }
            .portal-date-wrap .portal-date-input { padding-right: 2.25rem; }
            .flatpickr-calendar { z-index: 1090 !important; }
        </style>
    @endpush
    @push('scripts')
        <script src="{{ asset('theme/assets/libs/flatpickr/flatpickr.min.js') }}"></script>
        <script src="{{ asset('theme/assets/libs/flatpickr/l10n/fr.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof flatpickr === 'undefined') {
                    return;
                }

                flatpickr.localize(flatpickr.l10ns.fr);

                function initDateRange(range) {
                    var fromInput = range.querySelector('.portal-date-from, input[name="date_from"]');
                    var toInput = range.querySelector('.portal-date-to, input[name="date_to"]');

                    if (!fromInput) {
                        return;
                    }

                    var fromPicker = flatpickr(fromInput, {
                        locale: 'fr',
                        dateFormat: 'd/m/Y',
                        allowInput: true,
                        disableMobile: true,
                        appendTo: document.body,
                        onChange: function (selectedDates) {
                            if (toPicker) {
                                toPicker.set('minDate', selectedDates[0] || null);
                            }
                        },
                    });

                    var toPicker = null;

                    if (toInput) {
                        toPicker = flatpickr(toInput, {
                            locale: 'fr',
                            dateFormat: 'd/m/Y',
                            allowInput: true,
                            disableMobile: true,
                            appendTo: document.body,
                            onChange: function (selectedDates) {
                                fromPicker.set('maxDate', selectedDates[0] || null);
                            },
                        });

                        if (fromPicker.selectedDates[0]) {
                            toPicker.set('minDate', fromPicker.selectedDates[0]);
                        }
                        if (toPicker.selectedDates[0]) {
                            fromPicker.set('maxDate', toPicker.selectedDates[0]);
                        }
                    }
                }

                document.querySelectorAll('.portal-date-range').forEach(initDateRange);
            });
        </script>
    @endpush
@endonce
