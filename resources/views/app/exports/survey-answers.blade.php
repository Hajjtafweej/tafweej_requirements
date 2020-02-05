<table>
  <thead>
    <tr>
      @if(count($Heading))
      @foreach($Heading as $HeadingItem)
      <th>
        {{ $HeadingItem->title }}
      </th>
      @endforeach
      @endif
    </tr>
  </thead>
  <tbody>

  </tbody>
</table>
