<nav>
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="/">Home</a>
        </li>
        #anon()
        <li class="nav-item">
            <a class="nav-link" href="/register">Register</a>
        </li>
        <li class="nav-item float-right">
            <a class="nav-link" href="/login">login</a>
        </li>
        #endanon
        #auth()
        <li class="nav-item">
            <a class="nav-link" href="/logout">logout</a>
        </li>
        #endauth
    </ul>
</nav>
