@component('mail::message')
<div style="text-align: center;">
    <div style="font-size: 18px;margin-bottom: 10px;">قام المستخدم</div>
    <div style="font-size: 18px;color: #0da2b1;">{{ $User->name }}</div>
    <div style="font-size: 18px;margin-top: 20px;">بإكمال الأسئلة الإلزامية في أستبانة</div>
    <div style="font-size: 18px;color: #0da2b1;margin-top: 10px;">{{ $Survey->title_ar }}</div>
    <div style="padding-top: 10px;">
        @component('mail::button', ['url' => url('/panel#/admin/surveys/answers?survey_id='.$Survey->id)])
        استعراض الأجابات
        @endcomponent
    </div>
</div>
@endcomponent
