#inherit('layout/main.php')

#block('content')
<div>
    <table class="table">
        <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Title</th>
        </tr>
        </thead>
        <tbody>
        #foreach(id, title in books)
            <tr>
                <th scope="row">{{id}}</th>
                <td>{{title}}</td>
            </tr>
        #endforeach
        </tbody>
    </table>
</div>
#endblock
