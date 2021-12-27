#inherit('layout/main.php')

#block('content')
<div>
    #foreach(k, v in [2,34,5])
        <div>{{k}} -> {{v}}</div>
    #endforeach
</div>
#endblock
