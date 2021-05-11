@foreach($users as $user)
    <tr>
        <td>{{ $user->username }}</td>
        <td>{{ $user->firstname }}</td>
        <td>{{ $user->lastname }}</td>
        <td>{{ $user->email }}</td>
        <td>{{ $user->created_at->format('d/m/Y') }}</td>
    </tr>
@endforeach