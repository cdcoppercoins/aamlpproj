@extends('layouts.app')

@section('title', 'About | MiniLicensePlates.com')

@section('content')
<div class="set-width">

    <h1>About MiniLicensePlates.com</h1>

    <p>
        This project was created to show all known issues of miniature license plate toys issued with candy, gum or cereal for kids.
        It also touches on some of the items issued to be sold as bicycle vanity plates or other products.
    </p>

    <h2>About the collector</h2>

    <p>
        I have been a mini license plate collector since 1978 when I pulled my first plates out of Honey Comb cereal.
    </p>

    <p>
        Because of profuse color-blindness I was drawn to collecting items with lots of color—something that fit perfectly with miniature license plates.
    </p>

    <p>
        Over the years I've built a collection of more than 4,000 pieces that includes nearly all of the well-known sets and varieties.
    </p>

    <p>
        Today I focus on the rarest and most obscure items, which makes collection growth a very challenging adventure.
        While in this hunt, I continue to learn everything I can about how these plates were issued and manufactured, searching for documentation,
        news articles, and first-hand knowledge to compile the most comprehensive and supported history of the subject.
        I'm also working toward writing and publishing a checklist and price guide as a part of a book about all the documentation and history I can find.
    </p>

    <p>
        I built this website to help other collectors learn more about mini license plates and to share all I can find about the hobby.
    </p>

    <h2>What you'll find on this site</h2>
    <ul>
        <li>Complete visual guide to all known issues in chronological order</li>
        <li>Flip-over images on mouse-hover to show the back side of the plates</li>
        <li>Click to see large, clear images of each plate</li>
    </ul>

    <h2>Contribute</h2>
    <p>
        A websaite of this scope is very difficult to build alone. I have used the extensive knowledge of others and photographs from other people's
        collections to bring the site to the point it is now, and would appreciate your help. Use the <a href="{{ route('contribute') }}">contribution form</a>
        to send information or offers to help expand the library.
    </p>

    <h2>Community &amp; shop</h2>
    <ul>
        <li>Facebook group: <a href="https://www.facebook.com/groups/miniplates">https://www.facebook.com/groups/miniplates</a></li>
        <li>eBay store: <a href="https://www.ebay.com/str/minilicenseplates">https://www.ebay.com/str/minilicenseplates</a></li>
    </ul>

    <h2>Contact</h2>
    <p>
        Email: <a href="{{ route('contribute') }}">contact form</a><br>
        Or message via eBay<br>
        Postal address: Minilicenseplates, PO Box 2364, Smithfield, NC 27577
    </p>

</div>
@endsection
