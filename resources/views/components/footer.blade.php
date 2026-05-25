<!-- Footer -->
<footer class="site-footer">
    <div class="footer-frame">
        <div class="footer-main">
            <nav class="footer-col footer-col-left" aria-label="Collector resources">
                <ul class="footer-links">
                    <li><a href="#">Story of My Collecting</a></li>
                    <li><a href="#">Help Me With This Site</a></li>
                    <li><a href="#">Storing Your Plates</a></li>
                    <li><a href="#">Displaying a Collection</a></li>
                    <li><a href="#">Affiliate Links</a></li>
                    <li><a href="#">Join ALPCA</a></li>
                </ul>
            </nav>

            <div class="footer-col footer-col-center">
                <div class="footer-badge">
                    <img src="{{ asset('alpca_logo.png') }}"
                         alt="Automobile License Plate Collectors Association member"
                         class="footer-badge-img">
                    <p class="footer-badge-label">Member #14252</p>
                </div>
                <div class="footer-badge footer-badge-store">
                    <a href="https://www.ebay.com/str/minilicenseplates" target="_blank" rel="noopener">
                        <img src="{{ asset('avatar.png') }}"
                             alt="Mini License Plates"
                             class="footer-badge-img">
                    </a>
                    <p class="footer-badge-label">
                        <a href="https://www.ebay.com/str/minilicenseplates" target="_blank" rel="noopener">eBay Store</a>
                    </p>
                </div>
            </div>

            <nav class="footer-col footer-col-right" aria-label="Site information">
                <ul class="footer-links">
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Report Plagiarism</a></li>
                    <li><a href="#">Sign-up for News</a></li>
                </ul>
                <label class="footer-signup">
                    <span class="footer-signup-sr">Email address for news</span>
                    <input type="email"
                           class="footer-signup-input"
                           placeholder="enter email here"
                           disabled
                           aria-disabled="true">
                </label>
            </nav>

            <p class="footer-copyright">
                &copy;Copyright {{ date('Y') }} MiniLicensePlates.com &mdash; All images are owned by or licensed to
                MiniLicensePlates and may not be copied or redistributed in any form.
            </p>
        </div>
    </div>
</footer>
