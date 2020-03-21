<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>قائمة متطلبات {{ $Participant->name }}</title>
  <style type="text/css">
  body {
    height:100%;
    font-size: 16px;
    font-family: 'Frutiger',arial;
  }
  @page {
    margin-bottom: 100px;
  }
  .table {
    widtd: 100%;
    margin-bottom: 0;
  }
  .table td,.table td {
    padding: 0;
  }
  .right {
    float: right;
  }
  .left {
    float: left;
  }
  .mb-0 {
    margin-bottom: 0px !important;
  }
  .mb-1 {
    margin-bottom: 5px !important;
  }
  .mb-2 {
    margin-bottom: 10px !important;
  }
  .vtop {
    vertical-align: top;
  }
  .text-left {
    text-align: left;
  }
  .text-right {
    text-align: right;
  }
  .text-center {
    text-align: center;
  }
  .text-danger {
    color: #ea1111;
  }

  /* Table */
  
  .gray-bg {
    background: #e8e8e8;
  }
  .table {
    margin-bottom: 50px;
  }
  .table td {
    font-weight: bold;
    font-size: 16px;
    line-height: 1.6;
    padding: 15px 20px;

  }
  .table .border-bottom td {
    border-bottom: 1px solid #c5c5c5;
  }
  .table-heading th {
    background: #0692a0;
        border-bottom: 1px solid #077e8a;
    color: #fff;
        font-weight: bold;
    font-size: 16px;
    line-height: 1.6;
    padding: 10px 20px;
  }
  .table .border-l {
    border-left: 1px solid #c5c5c5;
  }
  .table .border-r {
    border-right: 1px solid #c5c5c5;
  }
  .table-heading .border-l {
    border-left: 1px solid #077e8a;
  }
  .table .border-t {
    border-top: 1px solid #c5c5c5;
  }

  .table-body-item .no {
    background: #f2f6f9;
  }


  /* Custom CSS */
  .page-title {
      text-align: center;
      font-size: 25px;
      margin-bottom: 50px;
      color: #078794;
  }

  .holy-place-title {
      font-size: 20px;
      margin-bottom: 20px;
  }

</style>
</head>
<body>
      <div class="page-title">
        قائمة متطلبات {{ $Participant->name }} <br> لموسم حج {{ \GeniusTS\HijriDate\Date::today()->format('Y') }} هـ
      </div>
  @if($HolyPlaces->count())
    @foreach($HolyPlaces as $HolyPlace)
        <div class="table">
          <table cellspacing="0" cellpadding="0">
            <thead class="table-heading">
              <tr>
                <th colspan="7" style="font-size: 25px;">{{ $HolyPlace->name }}</th>
</tr>
  <tr>              <th class="border-l border-r text-center no"  style="padding: 10px 20px;"></th>
              <th class="border-l" style="width: 120px">الموضوع</th>
              <th class="border-l" style="width: 120px">الموضوع الفرعي</th>
              <th class="border-l" style="width: 120px">المستوى</th>
              <th class="border-l" style="width: 120px">النطاق الجغرافي</th>
              <th class="border-l" style="width: 300px">مخرجات نطاق الاعمال</th>
              <th class="border-l" style="width: 300px">المتطلبات</th>
            </tr>
            </thead>
            @if($HolyPlace->Requirements->count())
            @foreach($HolyPlace->Requirements as $requirementKey => $Requirement)
            <tr class="table-body-item border-bottom">
              <td class="border-l border-r text-center no"  style="widtd: 30px;padding: 10px 0;">{{ $requirementKey+1 }}</td>
              <td class="border-l" style="widtd: 120px">{{ ($Requirement->Subject) ? $Requirement->Subject->name : '' }}</td>
              <td class="border-l" style="widtd: 120px">{{ ($Requirement->SubSubject) ? $Requirement->SubSubject->name : '' }}</td>
              <td class="border-l" style="widtd: 120px">{{ ($Requirement->Level) ? $Requirement->Level->name : '' }}</td>
              <td class="border-l" style="widtd: 120px">{{ ($Requirement->GeographicalScope) ? $Requirement->GeographicalScope->name : '' }}</td>
              <td class="border-l" style="widtd: 300px">{{ $Requirement->business_scope }}</td>
              <td class="border-l" style="width: 300px">{{ $Requirement->requirements }}</td>
            </tr>
            @endforeach
            @endif
          </table>
        </div>
    @endforeach
  @endif
</body>
</html>
