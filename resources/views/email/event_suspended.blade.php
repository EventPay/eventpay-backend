<p>&nbsp;</p>

<p>&nbsp;</p>

<p><span style="font-size:14px">Hello {{ \App\Models\User::find($event->organizer)->firstname }},<br />
        <br />
       Your event {{ $event->title }} has been suspended and all payments are currently on hold<br />

        Kindly contact support for resolution
    <br />
    &nbsp;
</p>
