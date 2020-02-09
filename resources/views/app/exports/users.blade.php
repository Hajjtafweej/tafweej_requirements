<table>
  <thead>
    <tr>
      <th>اسم المستخدم</th>
      <th>الأسم</th>
      <th>البريد الألكتروني</th>
      <th>نوع المستخدم</th>
      <th>تاريخ الإضافة</th>
    </tr>
  </thead>
  <tbody>
    @if(count($Users))
    @foreach($Users as $User)
    <tr>
      <td>{{ $User->username }}</td>
      <td>{{ $User->name }}</td>
      <td>{{ $User->email }}</td>
      <td>{{ $User->user_role_name }}</td>
      <td>{{ $User->created_at->format('Y/m/d') }}</td>
    </tr>
    @endforeach
    @endif
  </tbody>
</table>
