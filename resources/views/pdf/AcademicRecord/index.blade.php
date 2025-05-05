<style>
    * {
        font-family: 'Arial', sans-serif;

    }

    .table-heading {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .table-students {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .table-students th,
    .table-students td {
        border: 1px solid #000;
        padding: 5px;
    }

    .table-formula {
        width: 30%;
        border-collapse: collapse;
        border: 1px solid #000;
        font-size: 10px;
    }

    .table-formula th,
    .table-formula td {
        border: 1px solid #000;
        padding: 5px;
    }
</style>
<table class="table-heading">
    <tr>
        <td colspan="3"><strong>ACTA DE EXAMEN:</strong> REGULAR</td>
    </tr>
    <tr>
        <td colspan="3"><strong>CORRESPONDIENTE AL MES DE:</strong> {{ strtoupper($group->month) }}</td>
    </tr>
    <tr>
        <td colspan="3"><strong>CURSO:</strong> {{$group->course}}</td>
    </tr>
    <tr>
        <td colspan="3"><strong>DOCENTE:</strong> {{$group->teacher}}</td>
    </tr>
    <tr>
        <td style="width: 33%;"><strong>AÑO ACADÉMICO:</strong> {{$group->year}}</td>
        <td style="width: 33%;"><strong>Nivel/Módulo:</strong> {{$group->level}} </td>
        <td style="width: 33%;"><strong>Grupo:</strong> {{$group->name}}</td>
    </tr>
</table>

<table class="table-students" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th style="border: none;"></th>
            <th style="border: none;"></th>
            <th style="border: none;"></th>
            <th colspan="2">Promedio Parcial</th>
            <th colspan="2">Promedio Final</th>
        </tr>
        <tr>
            <th>N°</th>
            <th>CÓDIGO</th>
            <th>APELLIDOS Y NOMBRES</th>
            <th style="width: 50px; text-align: center;">Prom. Parc.</th>
            <th style="width: 50px; text-align: center;">Act.</th>
            <th style="width: 50px; text-align: center;">Número</th>
            <th><strong>LETRAS</strong></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($students as $i => $student)
        <tr>
            <td>
                {{ $i +1  }}
            </td>
            <td>
                {{ $student->code}}
            </td>
            <td>
                {{ $student->name }}
            </td>

            <td style="text-align: center;">
                @php
                $unit = collect($student->gradeUnits)->firstWhere('order', 1);
                @endphp

                {{ $unit->grade ?? '—' }}
            </td>
            <td style="text-align: center;">
                @php
                $unit = collect($student->gradeUnits)->firstWhere('order', 2);
                @endphp

                {{ $unit->grade !== null
                    ? ($unit->grade == 2 ? 'A' : ($unit->grade == 1 ? 'B' : 'C')) 
                    : '—' }}
            </td>
            <td style="text-align: center;">
                {{ $student->finalGrade }}
            </td>
            <td>
                {{ $student->finalGradeText }}
            </td>
        </tr>
        @endforeach

    </tbody>
</table>
<table class="table-formula" cellspacing="0">
    <thead>
        <tr>
            <th>(*) FORMULA DEL PONDERADO</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <p> PF= 0,9(PC)+ Actitudes PF=Promedio final </p>
                <p> PC=Promedio de las Capacidades </p>
                <p> Actitudes: A=2,B=1,C=0 </p>
            </td>
        </tr>
    </tbody>
</table>