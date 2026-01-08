<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === config('app.name'))
<img src="https://mhrpci.site/images/mhrhci.png" class="logo" alt="{{ config('app.name') }}" style="max-width: 200px; height: auto;">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
