<table>
  <thead>
    <tr>
      <th>اسم المفوج</th>
      <th>البريد الألكتروني</th>
      <th>رقم الجوال</th>
      <th>الدولة</th>
      <th>تاريخ الطلب</th>
    </tr>
  </thead>
  <tbody>
    @if(count($UserRegistrations))
    @foreach($UserRegistrations as $Registration)
    <tr>
      <td>{{ $Registration->delegation_name }}</td>
      <td>{{ $Registration->email }}</td>
      <td>{{ $Registration->phone }}</td>
      <td>{{ $Registration->country_name }}</td>
      <td>{{ $Registration->created_at->format('Y/m/d') }}</td>
    </tr>
    @endforeach
    @endif
  </tbody>
</table>
