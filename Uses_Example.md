// Get all students
$students = User::students()->get();

// Get all faculty
$faculty = User::faculty()->get();

// Get users in specific program
$cseStudents = User::inProgram(6)->get(); // Assuming program_id 6 is CSE

// Access program information on a user
$user = User::find(1);
echo $user->program_name; // "B.Sc. in CSE"
echo $user->program_code; // "006"
echo $user->program; // Program model instance