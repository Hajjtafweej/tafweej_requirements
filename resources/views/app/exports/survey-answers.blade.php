<table>
  <thead>
    <tr>
      <th>المستخدم</th>
      @if(count($Heading))
      @foreach($Heading as $HeadingItem)
      <th>
        {{ $HeadingItem }}
      </th>
      @endforeach
      @endif
    </tr>
  </thead>
  <tbody>
    @if(count($Answers))
    @foreach($Answers as $userAnswer)
    <tr>
      <td>{{ $userAnswer->username }} ({{ $userAnswer->name }})</td>
      @if(count($userAnswer->Answer))
        @foreach($userAnswer->Answer as $answerItem)
          @if($answerItem->LastAnswerValue)
            @if($answerItem->type == 'checkbox')
              <td>
                @foreach(collect($answerItem->Options)->whereIn('id',explode(',',$answerItem->LastAnswerValue->value))->all() as $checkboxOption)
                  {{ $checkboxOption->title }} @if(!$loop->last) - @endif
                @endforeach
              </td>
            @elseif($answerItem->LastAnswerValue->survey_question_option_id)
            @php $OptionItem = collect($answerItem->Options)->where('id',$answerItem->LastAnswerValue->survey_question_option_id)->first(); @endphp
              <td>{{ ($OptionItem) ? $OptionItem->title : '' }}</td>
            @else
              @switch($answerItem->type)
                @case('select_with_other')
                  @if($answerItem->LastAnswerValue->value == 'other')
                    <td>{{ $answerItem->LastAnswerValue->other_value }}</td>
                  @else
                    @php $OptionItem2 = collect($answerItem->Options)->where('id',$answerItem->survey_question_option_id)->first(); @endphp
                    <td>{{ ($OptionItem2) ? $OptionItem2->title : '' }}</td>
                  @endif
                @break
                @case('establishments_list')
                  <td>{{ __('lists.establishments.'.$answerItem->LastAnswerValue->value) }}</td>
                @break
                @case('hajj_days')
                  <td>{{ $answerItem->LastAnswerValue->value }} ذي الحجة</td>
                @break
                @case('makkah_towns_list')
                  <td>{{ __('lists.makkah_towns.'.$answerItem->LastAnswerValue->value) }}</td>
                @break
                @case('madinah_towns_list')
                  <td>{{ __('lists.madinah_towns.'.$answerItem->LastAnswerValue->value) }}</td>
                @break
                @case('ports_list')
                  <td>{{ __('lists.ports.'.$answerItem->LastAnswerValue->value) }}</td>
                @break
                @case('transportation_list')
                  <td>{{ __('lists.transportation.'.$answerItem->LastAnswerValue->value) }}</td>
                @break
                @default
                <td>{{ $answerItem->LastAnswerValue->value }}</td>
                @break
              @endswitch
            @endif
          @else
            <td></td>
          @endif
        @endforeach
      @endif
    </tr>
    @endforeach
    @endif
  </tbody>
</table>
