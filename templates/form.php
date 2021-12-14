#inherit('layout/main.php')

#block('content')
<form method="post" action="/form">
    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <div class="form-text text-danger">#flash(errors.email)</div>
        <input type="email" class="form-control" name="email" id="email" aria-describedby="emailHelp">
        <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
    </div>
    <div class="mb-3">
        <div class="form-text text-danger">#flash(errors.password)</div>
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password">
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
#if(! false)
<div>first</div>
#elif(false)
<div>second</div>
#elif(true)
<div>third</div>
#elif(false)
<div>fourth</div>
#endif
#endblock
