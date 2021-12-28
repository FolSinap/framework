#inherit('layout/main.php')

#block('content')
<div>
    <a href="/books/create" class="btn btn-success">New Book</a>
    <table class="table">
        <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Title</th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody>
        #foreach(id, title in books)
            <tr>
                <th scope="row">{{id}}</th>
                <td>{{title}}</td>
                <td><a href="/books/edit/{{id}}">Edit</a></td>
            </tr>
        #endforeach
        </tbody>
    </table>
</div>
#endblock
