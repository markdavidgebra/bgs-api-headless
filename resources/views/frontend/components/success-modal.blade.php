@if (! empty($message))
    @once
        @push('styles')
            <style>
                .bgs-success-modal {
                    position: fixed;
                    inset: 0;
                    z-index: 99999;
                    display: none;
                    place-items: center;
                    padding: 1.25rem;
                }

                .bgs-success-modal.is-open {
                    display: grid;
                }

                .bgs-success-modal__backdrop {
                    position: absolute;
                    inset: 0;
                    background: rgba(47, 35, 44, 0.45);
                    backdrop-filter: blur(2px);
                }

                .bgs-success-modal__dialog {
                    position: relative;
                    z-index: 1;
                    width: min(100%, 420px);
                    padding: 2rem 1.75rem 1.5rem;
                    border-radius: 16px;
                    background: #fff;
                    box-shadow: 0 24px 60px rgba(47, 35, 44, 0.18);
                    text-align: center;
                    animation: bgsModalFadeUp 0.45s cubic-bezier(0.22, 0.8, 0.36, 1) both;
                }

                .bgs-success-modal__icon {
                    display: grid;
                    place-items: center;
                    width: 64px;
                    height: 64px;
                    margin: 0 auto 1.25rem;
                    border-radius: 999px;
                    background: rgba(34, 150, 120, 0.12);
                    color: #1f8a6a;
                    font-size: 28px;
                    line-height: 1;
                }

                .bgs-success-modal__title {
                    margin: 0 0 0.75rem;
                    color: #2f232c;
                    font-size: 1.35rem;
                    font-weight: 700;
                    line-height: 1.3;
                }

                .bgs-success-modal__message {
                    margin: 0 auto 1.5rem;
                    max-width: 34ch;
                    color: #6d6a83;
                    font-size: 0.95rem;
                    line-height: 1.65;
                }

                .bgs-success-modal__actions {
                    display: flex;
                    flex-direction: column-reverse;
                    gap: 0.75rem;
                }

                @media (min-width: 480px) {
                    .bgs-success-modal__actions {
                        flex-direction: row;
                        justify-content: center;
                    }
                }

                .bgs-success-modal__btn {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 8.5rem;
                    padding: 0.85rem 1.35rem;
                    border-radius: 10px;
                    border: 1px solid transparent;
                    font-size: 0.95rem;
                    font-weight: 600;
                    line-height: 1.2;
                    text-decoration: none;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .bgs-success-modal__btn--ghost {
                    border-color: rgba(var(--bdr-color-rgb), 0.5);
                    background: rgba(var(--bdr-color-rgb), 0.35);
                    color: #2f232c;
                }

                .bgs-success-modal__btn--ghost:hover {
                    background: #fff;
                    border-color: var(--base);
                    color: var(--base);
                }

                .bgs-success-modal__btn--primary {
                    background: var(--base);
                    color: #fff;
                }

                .bgs-success-modal__btn--primary:hover {
                    opacity: 0.92;
                    color: #fff;
                }

                @keyframes bgsModalFadeUp {
                    from {
                        opacity: 0;
                        transform: translateY(18px) scale(0.98);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                }
            </style>
        @endpush

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('[data-bgs-success-modal]').forEach(function (modal) {
                        var closeModal = function () {
                            modal.classList.remove('is-open');
                            document.body.style.overflow = '';
                        };

                        modal.querySelectorAll('[data-bgs-success-modal-close]').forEach(function (trigger) {
                            trigger.addEventListener('click', closeModal);
                        });

                        modal.addEventListener('click', function (event) {
                            if (event.target === modal || event.target.classList.contains('bgs-success-modal__backdrop')) {
                                closeModal();
                            }
                        });

                        document.addEventListener('keydown', function (event) {
                            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                                closeModal();
                            }
                        });

                        modal.classList.add('is-open');
                        document.body.style.overflow = 'hidden';
                    });
                });
            </script>
        @endpush
    @endonce

    <div
        class="bgs-success-modal"
        data-bgs-success-modal
        role="dialog"
        aria-modal="true"
        aria-labelledby="bgs-success-modal-title"
        aria-describedby="bgs-success-modal-message"
    >
        <div class="bgs-success-modal__backdrop" aria-hidden="true"></div>

        <div class="bgs-success-modal__dialog">
            <div class="bgs-success-modal__icon" aria-hidden="true">✓</div>
            <h2 id="bgs-success-modal-title" class="bgs-success-modal__title">
                {{ $title ?? __('Registration submitted') }}
            </h2>
            <p id="bgs-success-modal-message" class="bgs-success-modal__message">
                {{ $message }}
            </p>

            <div class="bgs-success-modal__actions">
                <button type="button" class="bgs-success-modal__btn bgs-success-modal__btn--ghost" data-bgs-success-modal-close>
                    {{ $closeLabel ?? __('Close') }}
                </button>
                @if (! empty($loginUrl))
                    <a href="{{ $loginUrl }}" class="bgs-success-modal__btn bgs-success-modal__btn--primary">
                        {{ $loginLabel ?? __('Go to login') }}
                    </a>
                @else
                    <button type="button" class="bgs-success-modal__btn bgs-success-modal__btn--primary" data-bgs-success-modal-close>
                        {{ $primaryLabel ?? __('Got it') }}
                    </button>
                @endif
            </div>
        </div>
    </div>
@endif
