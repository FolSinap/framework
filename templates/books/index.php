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
                #auth()
                <td><a href="#route('books_edit', ['book' => {{book->id}}])">Edit</a></td>
                <td>
                    <form action="#route('books_delete', ['book' => {{book->id}}])" method="post">
                        #method('delete')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
                #endauth
            </tr>
        #endforeach
        </tbody>
    </table>
</div>
#endblock
