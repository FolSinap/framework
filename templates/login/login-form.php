#inherit('layout/main.php')

#block('content')
<form method="post" action="#route('login')">
    #csrf()
    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <div class="form-text text-danger">#flash('errors.email')</div>
        <input  class="form-control" name="email" id="email" aria-describedby="emailHelp">
        <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
    </div>
    <div class="mb-3">
        <div class="form-text text-danger">#flash('errors.password')</div>
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password">
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
#endblock
