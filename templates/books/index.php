#inherit('layout/main.php')

#block('content')
<div>
    <a href="#route('books_create')" class="btn btn-success">New Book</a>
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
                <td><a href="#route('books_edit', ['book' => {{id}}])">Edit</a></td>
                <td>
                    <form action="#route('books_delete', ['book' => {{id}}])" method="post">
                        #method('delete')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        #endforeach
        </tbody>
    </table>
</div>
#endblock
