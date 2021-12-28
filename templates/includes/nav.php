<nav>
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#route('main')">Home</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#route('books_index')">Books</a>
        </li>
        #auth()
        <li class="nav-item">
            <a class="nav-link" href="#route('logout')">logout</a>
        </li>
        #endauth
        #anon()
        <li class="nav-item">
            <a class="nav-link" href="#route('register_form')">Register</a>
        </li>
        <li class="nav-item float-right">
            <a class="nav-link" href="#route('login_form')">login</a>
        </li>
        #endanon
    </ul>
</nav>
