        
        <page>
            <h1>Daftar Option</h1>
            <hr />
            <ol>
                @foreach ($options as $option)
                <table border="1">
                    <thead>
                        <tr bgcolor="yellow">
                            <td>Title</td>
                            <td>Slogan</td>
                            <td>Description</td>
                            <td>Keyword</td>
                            <td>Footer</td>
                            <td>Logo</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{$option->title}}</td>
                            <td>{{$option->slogan}}</td>
                            <td>{{$option->description}}</td>
                            <td>{{$option->keyword}}</td>
                            <td>{{$option->footer}}</td>
                            <td><img src="{{URL::to('assets/admin/images/logo/'.$option->logo)}}" alt="" width="30px" height="10px"></td>
                        </tr>
                    </tbody>
                </table>
                @endforeach
            </ol>
        </page>
