{{-- Per-row Source cell for the order index. Pass $item as the order. --}}
<td class="px-1" style="max-width: 110px;">
    @php
        $src = $item->order_source ?? null;
    @endphp

    @if(empty($src))
        <span class="text-muted" style="font-size: 11px;">—</span>
    @else
        @php
            $srcLower = strtolower($src);
            $srcIcon = 'mdi-web';
            $srcBg = '#6c757d';

            if (str_contains($srcLower, 'tiktok'))       { $srcIcon = 'mdi-music-note-eighth'; $srcBg = '#000'; }
            elseif (str_contains($srcLower, 'facebook')) { $srcIcon = 'mdi-facebook'; $srcBg = '#1877F2'; }
            elseif (str_contains($srcLower, 'instagram')){ $srcIcon = 'mdi-instagram'; $srcBg = '#E1306C'; }
            elseif (str_contains($srcLower, 'google'))   { $srcIcon = 'mdi-google'; $srcBg = '#4285F4'; }
            elseif (str_contains($srcLower, 'youtube'))  { $srcIcon = 'mdi-youtube'; $srcBg = '#FF0000'; }
            elseif (str_contains($srcLower, 'whatsapp')) { $srcIcon = 'mdi-whatsapp'; $srcBg = '#25D366'; }
            elseif (str_contains($srcLower, 'twitter'))  { $srcIcon = 'mdi-twitter'; $srcBg = '#1DA1F2'; }
            elseif (str_contains($srcLower, 'direct'))   { $srcIcon = 'mdi-cursor-default-click'; $srcBg = '#6c757d'; }
            elseif (str_contains($srcLower, 'referral')) { $srcIcon = 'mdi-link-variant'; $srcBg = '#fd7e14'; }
            else { $srcIcon = 'mdi-web'; $srcBg = '#20c997'; }

            $hoverTxt = '';
            if (!empty($item->utm_campaign))      $hoverTxt .= 'Campaign: ' . $item->utm_campaign . "\n";
            if (!empty($item->utm_medium))        $hoverTxt .= 'Medium: '   . $item->utm_medium   . "\n";
            if (!empty($item->landing_page_type)) $hoverTxt .= 'Page: LP-'  . $item->landing_page_type . "\n";
            if (!empty($item->referer_url))       $hoverTxt .= 'From: '     . $item->referer_url;
        @endphp
        <span class="badge d-inline-flex align-items-center"
              style="background: {{ $srcBg }} !important; color: #fff !important; font-size: 9px; padding: 3px 6px; gap: 3px; white-space: nowrap;"
              title="{{ trim($hoverTxt) ?: $src }}">
            <i class="mdi {{ $srcIcon }}" style="font-size: 11px; color: #fff !important;"></i>
            <span style="color: #fff !important;">{{ $src }}</span>
        </span>

        @if(!empty($item->landing_page_type))
            <div class="text-muted mt-1" style="font-size: 9px;"><i class="mdi mdi-tag-outline"></i> LP-{{ $item->landing_page_type }}</div>
        @endif
    @endif
</td>
