<table style="width: 100%; font-size: 12px; border-collapse: collapse; border: none;">
    <tbody>
        <tr>
            <td style="width: 33%;">
                <i>
                    {{ $userInitials }}
                </i>
            </td>
            <td style="width: 33%; text-align: center;">
                <i>
                    Puno, {{ \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e\l Y\, h:i A') }}
                </i>
            </td>
            <td style="width: 33%; text-align: right;">
                Página {PAGENO} / {nbpg}
            </td>
        </tr>
    </tbody>
</table>