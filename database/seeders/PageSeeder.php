<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        if (! Page::query()->where('slug', 'about')->exists()) {
            Page::query()->create([
                'slug' => 'about',
                'title' => 'About MiniLicensePlates.com',
                'meta_description' => 'Learn about MiniLicensePlates.com, a reference project documenting miniature license plate toys from Post, Topps, cereal premiums, and related collectibles.',
                'body' => <<<'HTML'
<p>This project was created to show all known issues of miniature license plate toys issued with candy, gum or cereal for kids. It also touches on some of the items issued to be sold as bicycle vanity plates or other products.</p>

<h2>About the collector</h2>

<p><img src="/proheadshot.png" alt="Portrait of C. Daughtrey, miniature license plate collector" width="220" height="220" class="static-page-img-left">I have been a mini license plate collector since 1978 when I pulled my first plates out of Honey Comb cereal.</p>

<p>Because of profuse color-blindness I was drawn to collecting items with lots of color—something that fit perfectly with miniature license plates.</p>

<p>Over the years I've built a collection of more than 4,000 pieces that includes nearly all of the well-known sets and varieties.</p>

<p>Today I focus on the rarest and most obscure items, which makes collection growth a very challenging adventure. While in this hunt, I continue to learn everything I can about how these plates were issued and manufactured, searching for documentation, news articles, and first-hand knowledge to compile the most comprehensive and supported history of the subject. I'm also working toward writing and publishing a checklist and price guide as a part of a book about all the documentation and history I can find.</p>

<p>I built this website to help other collectors learn more about mini license plates and to share all I can find about the hobby.</p>

<h2>What you'll find on this site</h2>
<ul>
<li>Complete visual guide to all known issues in chronological order</li>
<li>Flip-over images on mouse-hover to show the back side of the plates</li>
<li>Click to see large, clear images of each plate</li>
</ul>

<h2>Contribute</h2>
<p>A website of this scope is very difficult to build alone. I have used the extensive knowledge of others and photographs from other people's collections to bring the site to the point it is now, and would appreciate your help. Use the <a href="/contribute">contribution form</a> to send information or offers to help expand the library.</p>

<h2>Community &amp; shop</h2>
<ul>
<li>Facebook group: <a href="https://www.facebook.com/groups/miniplates">https://www.facebook.com/groups/miniplates</a></li>
<li>eBay store: <a href="https://www.ebay.com/str/minilicenseplates">https://www.ebay.com/str/minilicenseplates</a></li>
</ul>

<h2>Contact</h2>
<p>Email: <a href="/contribute">contact form</a><br>Or message via eBay<br>Postal address: Minilicenseplates, PO Box 2364, Smithfield, NC 27577</p>
HTML,
                'sort_order' => 1,
                'is_published' => true,
            ]);
        }

        if (! Page::query()->where('slug', 'terms-of-service')->exists()) {
            Page::query()->create([
                'slug' => 'terms-of-service',
                'title' => 'Terms of Service',
                'meta_description' => 'Terms of Service for MiniLicensePlates.com — use of the catalog, gallery, member collection tools, and site content.',
                'body' => <<<'HTML'
<p><strong>Effective date:</strong> May 28, 2026</p>

<p>These Terms of Service (&ldquo;Terms&rdquo;) govern your access to and use of MiniLicensePlates.com (the &ldquo;Site&rdquo;), operated by MiniLicensePlates (&ldquo;we,&rdquo; &ldquo;us,&rdquo; or &ldquo;our&rdquo;). By using the Site, you agree to these Terms. If you do not agree, please do not use the Site.</p>

<h2>1. What this site provides</h2>
<p>MiniLicensePlates.com is a reference and community resource for collectors of miniature license plate toys and related items. The Site may include a visual catalog and gallery, historical articles, search tools, catalog value estimates, optional member accounts for tracking personal collections, a contribution form, newsletter signup, and links to third-party sites such as our eBay store and Facebook group.</p>
<p>We may add, change, or remove features at any time without notice.</p>

<h2>2. Information is for reference only</h2>
<p>Catalog listings, descriptions, issue dates, dimensions, pricing columns, and other reference material on the Site are provided for educational and collecting purposes. We work to keep information accurate and current, but we do not guarantee that any content is complete, correct, or up to date.</p>
<p>Catalog values and condition grades (such as MT, EX, VG, G, FR, and PO) are <strong>estimates only</strong>. Actual market prices depend on condition, demand, venue, and many other factors. Do not rely on Site content as financial, legal, or professional advice.</p>

<h2>3. Accounts and your collection data</h2>
<p>Some features require a free member account. You are responsible for keeping your login credentials confidential and for all activity under your account.</p>
<p>Information you enter into your personal collection (quantities, grades, notes, and similar data) is stored to provide the service to you. You may choose to share certain collection information with other members where the Site offers that option. Do not enter information you are not permitted to share.</p>
<p>We may suspend or terminate accounts that violate these Terms or that we reasonably believe are being misused.</p>

<h2>4. Your contributions</h2>
<p>If you submit information, images, corrections, or other material through the contribution form or otherwise (&ldquo;Submissions&rdquo;), you represent that you have the right to provide that material and that it does not infringe anyone else&rsquo;s rights.</p>
<p>You grant us a non-exclusive, royalty-free license to use, reproduce, adapt, publish, and display Submissions in connection with operating and improving the Site and documenting the hobby. We are not obligated to use any Submission.</p>

<h2>5. Site content and images</h2>
<p>Unless otherwise noted, text, photographs, scans, graphics, layout, and other content on the Site are owned by or licensed to MiniLicensePlates and are protected by copyright and other laws.</p>
<p>You may view and print pages for personal, non-commercial reference. You may not copy, scrape, redistribute, sell, or create derivative works from Site content without our prior written permission, except for brief quotations with attribution or as allowed by law.</p>

<h2>6. Third-party links and services</h2>
<p>The Site may link to third-party websites or services (for example, eBay, Facebook, or advertising partners). Those sites have their own terms and privacy practices. We do not control and are not responsible for third-party content, transactions, or policies.</p>
<p>Purchases made through our eBay store or other outside channels are governed by the applicable platform&rsquo;s terms, not these Terms.</p>

<h2>7. Acceptable use</h2>
<p>You agree not to:</p>
<ul>
<li>Use the Site in any unlawful way or to harass, abuse, or harm others</li>
<li>Attempt to gain unauthorized access to the Site, other accounts, or our systems</li>
<li>Interfere with the Site&rsquo;s operation or impose an unreasonable load on our infrastructure</li>
<li>Use automated means to scrape or download large portions of the Site without permission</li>
<li>Misrepresent your identity or affiliation</li>
</ul>

<h2>8. Disclaimer of warranties</h2>
<p>The Site is provided &ldquo;as is&rdquo; and &ldquo;as available.&rdquo; To the fullest extent permitted by law, we disclaim all warranties, express or implied, including warranties of merchantability, fitness for a particular purpose, and non-infringement.</p>

<h2>9. Limitation of liability</h2>
<p>To the fullest extent permitted by law, MiniLicensePlates and its operator will not be liable for any indirect, incidental, special, consequential, or punitive damages, or for any loss of data, profits, or goodwill, arising from your use of the Site or reliance on Site content.</p>
<p>Our total liability for any claim relating to the Site will not exceed the greater of (a) the amount you paid us, if any, in the twelve months before the claim, or (b) fifty U.S. dollars ($50).</p>

<h2>10. Indemnity</h2>
<p>You agree to indemnify and hold harmless MiniLicensePlates and its operator from claims, damages, and expenses (including reasonable attorneys&rsquo; fees) arising from your use of the Site, your Submissions, or your violation of these Terms.</p>

<h2>11. Changes</h2>
<p>We may update these Terms from time to time. The &ldquo;Effective date&rdquo; at the top will change when we do. Continued use of the Site after changes are posted means you accept the revised Terms.</p>

<h2>12. Governing law</h2>
<p>These Terms are governed by the laws of the State of North Carolina, United States, without regard to conflict-of-law rules. Any dispute arising from these Terms or the Site will be brought in the state or federal courts located in North Carolina, and you consent to that jurisdiction.</p>

<h2>13. Contact</h2>
<p>Questions about these Terms may be sent through our <a href="/contribute">contact form</a> or by mail to Minilicenseplates, PO Box 2364, Smithfield, NC 27577.</p>
HTML,
                'sort_order' => 2,
                'is_published' => true,
            ]);
        }

        if (! Page::query()->where('slug', 'privacy-policy')->exists()) {
            Page::query()->create([
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'meta_description' => 'Privacy Policy for MiniLicensePlates.com — how we collect, use, and protect information when you use the site.',
                'body' => <<<'HTML'
<p><strong>Effective date:</strong> May 28, 2026</p>

<p>MiniLicensePlates (&ldquo;we,&rdquo; &ldquo;us,&rdquo; or &ldquo;our&rdquo;) operates MiniLicensePlates.com (the &ldquo;Site&rdquo;). This Privacy Policy explains what information we collect, how we use it, and the choices you have. By using the Site, you agree to this policy. Please also read our <a href="/terms-of-service">Terms of Service</a>.</p>

<h2>1. Information we collect</h2>

<h3>Information you provide</h3>
<ul>
<li><strong>Member account:</strong> When you register, we collect your display name, username, email address, and password (stored in hashed form). You may optionally add a phone number, mailing address, and profile photo in your profile settings.</li>
<li><strong>Collection data:</strong> If you use My Collection, we store the catalog entries, quantities, condition grades, notes, and related information you enter. You may choose to mark individual sets as public so other members can view them.</li>
<li><strong>Newsletter:</strong> If you subscribe in the footer, we store your email address to send updates and occasional advertising about the hobby or Site.</li>
<li><strong>Contribution form:</strong> If you contact us through the contribute form, we receive your name, email address, and message. Our notification email may also include your IP address to help identify spam or abuse.</li>
</ul>

<h3>Information collected automatically</h3>
<ul>
<li><strong>Log data:</strong> Like most websites, our hosting provider may record technical information such as your IP address, browser type, referring page, and the date and time of requests. We use this for security, troubleshooting, and understanding general Site usage.</li>
<li><strong>Cookies and similar technologies:</strong> The Site uses essential cookies to keep you signed in (for example, a session cookie when you log in). If Google AdSense advertising is enabled on the Site, Google and its partners may use cookies or similar technologies to serve and measure ads. See Google&rsquo;s policies at <a href="https://policies.google.com/privacy">https://policies.google.com/privacy</a> and <a href="https://policies.google.com/technologies/ads">https://policies.google.com/technologies/ads</a>.</li>
</ul>

<h2>2. How we use information</h2>
<p>We use the information described above to:</p>
<ul>
<li>Provide and maintain the Site, including catalog browsing, search, gallery, history, and articles</li>
<li>Operate member accounts and personal collection tools</li>
<li>Display collection information you choose to make public to other members</li>
<li>Respond to messages and contribution submissions</li>
<li>Send newsletter emails to subscribers</li>
<li>Protect the Site against spam, fraud, and abuse</li>
<li>Improve content and features</li>
</ul>
<p>We do not sell your personal information.</p>

<h2>3. When we share information</h2>
<p>We may share information in these limited situations:</p>
<ul>
<li><strong>Public collection sharing:</strong> If you mark a set as public, other signed-in members may see your username, profile photo (if set), and the collection details you have recorded for that set.</li>
<li><strong>Service providers:</strong> We use hosting, email, and related services to run the Site. Those providers process data on our behalf and only as needed to provide their services.</li>
<li><strong>Legal requirements:</strong> We may disclose information if required by law or if we believe disclosure is necessary to protect our rights, users, or the public.</li>
<li><strong>Business changes:</strong> If the Site or its assets are transferred, information may be included as part of that transaction, subject to this policy.</li>
</ul>
<p>Links to third-party sites (such as eBay or Facebook) are governed by those sites&rsquo; own privacy policies, not this one.</p>

<h2>4. How long we keep information</h2>
<p>We retain account and collection data while your account is active. Newsletter addresses are kept until you ask to be removed or we discontinue the list. Contribution messages sent by email are kept only as long as needed to respond and maintain our records. Server logs are retained for a limited period appropriate for security and operations.</p>

<h2>5. Security</h2>
<p>We use reasonable administrative and technical measures to protect information, including hashed passwords and access controls for administrative areas. No method of transmission or storage is completely secure, and we cannot guarantee absolute security.</p>

<h2>6. Your choices</h2>
<ul>
<li><strong>Profile:</strong> Signed-in members can update name, email, phone, address, and profile photo under Profile.</li>
<li><strong>Public sharing:</strong> You control whether each collection set is public or private in My Collection.</li>
<li><strong>Newsletter:</strong> To unsubscribe, contact us through the <a href="/contribute">contact form</a> using the email address you subscribed with.</li>
<li><strong>Account deletion:</strong> To request deletion of your account and associated personal data, contact us through the <a href="/contribute">contact form</a>. We may retain certain information where required by law or for legitimate business records.</li>
</ul>

<h2>7. Children</h2>
<p>The Site is a general-audience reference site for collectors and is not directed to children under 13. We do not knowingly collect personal information from children under 13. If you believe a child has provided us personal information, please contact us so we can delete it.</p>

<h2>8. Changes to this policy</h2>
<p>We may update this Privacy Policy from time to time. The effective date at the top will change when we do. Continued use of the Site after changes are posted means you accept the updated policy.</p>

<h2>9. Contact</h2>
<p>Privacy questions or requests may be sent through our <a href="/contribute">contact form</a> or by mail to Minilicenseplates, PO Box 2364, Smithfield, NC 27577.</p>
HTML,
                'sort_order' => 3,
                'is_published' => true,
            ]);
        }
    }
}
