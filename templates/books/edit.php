#inherit('layout/main.php')

#block('content')
<form action="/books/edit/{{id}}" method="post">
    <input type="hidden" name="_method" value="PATCH">
    <div class="form-group">
        <label for="title">Title</label>
        <div class="form-text text-danger">#flash(errors.title)</div>
        <input class="form-control" name="title" id="title" placeholder="Title" value="{{title}}">
    </div>
    <br>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
#endblock

