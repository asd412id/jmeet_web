<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
      html,body{
        font-family: Arial;
        font-size: 13px;
      }
      .center{
        text-align: center;
      }
      .left{
        text-align: left;
      }
      .right{
        text-align: right;
      }
      .table th, .table td{
        padding: 3px 7px;
      }
      .table td{
        vertical-align: top;
      }
      .nowrap{
        white-space: nowrap;
      }
    </style>
  </head>
  <body>
    <h3 class="center" style="padding: 0;margin: 0">DAFTAR HADIR</h3>
    <h2 class="center" style="padding: 0;margin: 0">{{ $title }}</h2>
    <p class="center" style="margin-top: 0">{{ $data->desc }}</p>
    @php
    $tgl_start = \Carbon\Carbon::parse($data->start)->locale('id')->translatedFormat("j F Y");
    $tgl_end = \Carbon\Carbon::parse($data->end)->locale('id')->translatedFormat("j F Y");
    $tm_start = \Carbon\Carbon::parse($data->start)->locale('id')->translatedFormat("H:i");
    $tm_end = \Carbon\Carbon::parse($data->end)->locale('id')->translatedFormat("H:i");

    if ($tgl_start == $tgl_end) {
      $tgl = $tgl_start;
    }else{
      $tgl = $tgl_start.' s.d. '.$tgl_end;
    }

    if ($tm_start == $tm_end) {
      $tm = $tm_start;
    }else{
      $tm = $tm_start.' s.d. '.$tm_end;
    }
    @endphp
    <table>
      <tr>
        <td>Tanggal</td>
        <td>:</td>
        <th class="left">{{ $tgl }}</th>
      </tr>
      <tr>
        <td>Pukul</td>
        <td>:</td>
        <th class="left">{{ $tm }}</th>
      </tr>
    </table>
    <table class="table" style="width: 100%" border="1" style="border-collapse: collapse;border: solid 1px" cellspacing="0">
      <tr>
        <th>NO</th>
        <th>NAMA</th>
        <th>NO. TELP</th>
        <th>EMAIL</th>
        <th>TTD MULAI</th>
        <th>TTD SELESAI</th>
      </tr>
      <tbody>
        @foreach ($data->join_meet as $key => $jm)
          <tr>
            <td class="center">{{ $key+1 }}</td>
            <td class="nowrap">{{ $jm->user_data['name'] }}</td>
            <td>{{ $jm->user_data['telp'] }}</td>
            <td>{{ $jm->user_data['email'] }}</td>
            <td class="center">
              <p style="font-size: 0.7em">{{ $jm->start?\Carbon\Carbon::parse($jm->start)->locale('id')->translatedFormat("j F Y H:i:s"):'-' }}</p>
              @if ($jm->start)
                <p><img src="{{ \Storage::url('ttd/signin_'.$jm->uuid.'.png') }}" alt="" width="75" height="75"></p>
              @endif
            </td>
            <td class="center">
              <p style="font-size: 0.7em">{{ $jm->end?\Carbon\Carbon::parse($jm->end)->locale('id')->translatedFormat("j F Y H:i:s"):'-' }}</p>
              @if ($jm->end)
                <p><img src="{{ \Storage::url('ttd/signout_'.$jm->uuid.'.png') }}" alt="" width="75" height="75"></p>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <table style="width: 100%">
      <tr>
        <td class="right">
          <table class="left" style="">
            <tr>
              <td style="padding-top: 15px">..................., {{ $tgl_end }}</td>
            </tr>
            <tr>
              <td style="padding-top: 95px">(................................................)</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>
