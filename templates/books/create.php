#inherit('layout/main.php')

#block('content')
<form action="#route('books_store')" method="post">
    <div class="form-group">
        <label for="title">Title</label>
        <div class="form-text text-danger">#flash('errors.title')</div>
        <input class="form-control" name="title" id="title" placeholder="Title">
    </div>
    <br>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
#endblock
