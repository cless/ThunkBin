    <div class="box">
        <p class="question">What is ThunkBin?</p>
        <p class="answer">ThunkBin is an attempt to improve on the other pastebins you find online, its key feature at this moment is multi-file pastes. It is still a work in progress, over time it will hopefully evolve to bring you the best pastebin experience you can find.</p>

        <p class="question">Is the source available?</p>
        <p class="answer">Certainly! There is a <a href="https://github.com/cless/ThunkBin">github repository</a> with the source code. The source is released under the <a href="https://secure.wikimedia.org/wikipedia/en/wiki/MIT_License">MIT license</a>.</p>

        <p class="question">Why is there no highlighting? Why is feature X missing?</p>
        <p class="answer">ThunkBin is still very young, code highlighting is going to be implemented in the near future. Many other features are planned too: accounts, phpbb account integration, diffs, raw file downloads, zip downloads, etc... If you have any suggestions or feature requests you can always create an issue on the <a href="https://github.com/cless/ThunkBin/issues">github issue tracker</a> or you can post in <a href="https://thunked.org/programming/seeking-input-on-pastebin-database-layout-t137.html">this thread</a> on the Thunked forum (guest posting is enabled).</p>
        
        <p class="question">What's the difference between public, private and encrypted pastes?</p>
        <p class="answer">Public pastes are visible to everyone, people can browse through all public pastes via the public paste listing. Private pastes are much like public pastes, but only people who know the url can access them. They are never linked to from anywhere on the site.<br />Encrypted pastes are stored with 256 bit AES encryption and can only be viewed after providing the passphrase required to decrypt the paste. Not just the pastes contents are encrypted, but all the meta data surounding the paste are encrypted as well (title, author, filenames). I cannot myself read any encrypted paste if you provided a strong enough password, but that does not mean your pastes are 100% secure. My hosting provider could sniff your POST data and log the password when you first paste it, or when you try to decrypt it. A judge could potentially order me to silently log the password of a certain paste. With this information you can judge if encrypted pastes are secure enough for your threat model. </p>
    </div>
