<div>
    <p>List of students</p><!-- No surplus words or unnecessary actions. - Marcus Aurelius -->
    @foreach($students as $student)
        <li>
            <p>Student Number: </p>
            <p>Name: {{$student->first_name}} {{$student->last_name}}</p>
            <p>Program: {{$student->course}}</p>
            <p>Year Level: {{$student->year_level}}</p>
        </li>
    @endforeach
</div>
