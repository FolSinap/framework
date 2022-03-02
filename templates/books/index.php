#inherit('layout/main.php')

#block('content')
<div>
    #auth()
    <a href="#route('books_create')" class="btn btn-success">New Book</a>
    #endauth
    <table class="table">
        <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Title</th>
            <th scope="col">Author</th>
            <th scope="col">Genres</th>
            <th scope="col"></th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody>
        #foreach(book in books)
            <tr>
                <th scope="row">{{book->id}}</th>
                <td>{{book->title}}</td>
                <td>{{book->author ? book->author->email : ''}}</td>
                <td>#foreach(genre in book->genres) {{genre->name}} #endforeach</td>
                #auth()
                <td>
                    #guard('books:manage', book)
                        <a href="#route('books_edit', ['book' => {{book->id}}])">Edit</a>
                    #endguard
                </td>
                <td>
                    #guard('books:manage', book)
                        <form action="#route('books_delete', ['book' => {{book->id}}])" method="post">
                            #csrf()
                            #method('delete')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    #endguard
                </td>
                #endauth
            </tr>
        #endforeach
        </tbody>
    </table>
</div>
#endblock
