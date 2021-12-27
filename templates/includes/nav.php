<nav>
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="/">Home</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/books">Books</a>
        </li>
        #auth()
        <li class="nav-item">
            <a class="nav-link" href="/logout">logout</a>
        </li>
        #endauth
        #anon()
        <li class="nav-item">
            <a class="nav-link" href="/register">Register</a>
        </li>
        <li class="nav-item float-right">
            <a class="nav-link" href="/login">login</a>
        </li>
        #endanon
    </ul>
</nav>
