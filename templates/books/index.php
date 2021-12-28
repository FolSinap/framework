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
            <th scope="col"></th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody>
        #foreach(id, title in books)
            <tr>
                <th scope="row">{{id}}</th>
                <td>{{title}}</td>
                #auth()
                <td><a href="#route('books_edit', ['book' => {{id}}])">Edit</a></td>
                <td>
                    <form action="#route('books_delete', ['book' => {{id}}])" method="post">
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
