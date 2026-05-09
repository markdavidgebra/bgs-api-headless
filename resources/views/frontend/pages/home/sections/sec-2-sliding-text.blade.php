<section class="sliding-text">
    @php($promotions = $homePromotions ?? collect())
    <div class="sliding-text__inner">
        <ul class="sliding-text__list marquee_mode-1 list-unstyled">
            @forelse ($promotions as $promo)
                <li>
                    <p>
                        <strong>{{ $promo->name }}</strong>
                        @if (filled($promo->validity_label))
                            - Valid: {{ $promo->validity_label }}
                        @else
                            - Valid: —
                        @endif
                    </p>
                </li>
            @empty
                <li><p>No active promotions available right now. Check back soon for new offers.</p></li>
            @endforelse
        </ul>
    </div>
</section>