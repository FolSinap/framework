#inherit('layout/main.php')

#block('content')
<form action="#route('books_store')" method="post">
    #csrf()
    <div class="form-group">
        <label for="title">Title</label>
        <div class="form-text text-danger">#flash('errors.title')</div>
        <input class="form-control" name="title" id="title" placeholder="Title">
    </div>
    <div class="form-group">
        <label for="genres">Genres</label>
        <div class="form-text text-danger">#flash('errors.genres')</div>
        <select name="genres[]" id="genres" multiple>
            #foreach(genre in genres)
                <option value="{{genre->id}}">{{genre->name}}</option>
            #endforeach
        </select>
    </div>
    <br>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
#endblock
