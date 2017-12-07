# Villain
A small PHP library used to help build any form of application.

# How to use
Simply extract Villain to any directory, and include the root file Villain.php.
Villain.php will autoload any Villain classes you chose to use automatically.
**Make sure to `use` the correct namespaces**

# Routing
Routing can be used to control the flow of any request.
This can be especially useful for handling requests using specific methods, e.g. calling the url `/user/Dec` with the `GET` method could return all the user 'Dec's information. Whereas, calling the same URL with the `DELETE` or `PATCH` methods may delete the user, or apply some new information to the user, respectively.

## How to use
Simply pass the pattern and promise (or closure) into any of the `Route` static methods `Delete, Get, Patch, Post, Put, Any` or `Add` with an array of the methods you intend to support.

## Routing Example
```
Router::Delete(
	"/users/{id}",
	function($id)
	{
		echo "Hi user " . $id;
	}
);

try
{
	$output = Router::Route("delete", "/users/1");

	Response::SetStatusCode(StatusCode::OK);
}
catch(RouterException $e)
{
	$output = json_encode($e);

	Response::SetStatusCode(StatusCode::NOT_FOUND);
}

die($output);
```

# Templating
Templating can be used to create dynamic data output by submitting data to a template file and rendering it.

## How to use
Simply `use Villain\Output\Templating\Template;` and create a new `Template` object.
If you wish to load directly from a file, you can pass the path to the file via the method `LoadFile` as such `$template->LoadFile("./Templates/Home.tpl");`, or simply pass it via the `content` parameter of the construcor `new Template(file_get_contents("./Templates/Home.tpl"));`.

Templating currently supports 3 types of data:
### Plain Data
Data which will simply be output as is and start with the tag
### Variable statements
Statements which will be evaluated, modified (if a modifier is supplied) and the value output in place
### Expression statements
Statements which include things such as if and foreach.

## Starting/Ending Tags
Starting and Ending tags surround the type of data it represents. These can be changed by passing respective values to the Lexer::Lex `options` parameter.

Defaults:
Plain Data - NONE
Variables - `{{` and `}}`
Expressions - `{%` and `%}`

## Templating Example
Top5Games.php (snippet)
```
$userGrades = array(
	"ADMIN" => 1
);

$template = new Template("./Templates/Top5Games.tpl");

$template->user_grades = $userGrades;

$template->user = array(
	"names" => array(
		"username" => "Dec",
		"first" => "Declan",
		"surname" => "Murphy",
		"grade" => $userGrades["ADMIN"]
	)
);

$template->games = array(
	3 => array(
		"is_kill_based" => false,
		"is_score_based" => true,
		"is_time_based" => false,
		"time_in_objective" => 0,
		"score" => 100,
		"kills" => array(
			array(
				"name" => "Abi"
			),
			array(
				"name" => "Zoe"
			)
		)
	),
	2 => array(
		"is_kill_based" => false,
		"is_score_based" => false,
		"is_time_based" => true,
		"time_in_objective" => 156,
		"score" => 100,
		"kills" => array(
			array(
				"name" => "Dom"
			),
			array(
				"name" => "Dave"
			)
		)
	),
	1 => array(
		"is_kill_based" => true,
		"is_score_based" => false,
		"is_time_based" => false,
		"time_in_objective" => 0,
		"score" => 30,
		"kills" => array(
			array(
				"name" => "Erblin"
			)
		)
	)
);

try
{
	$data = $template->Render();
}
catch(EvaluatorException $exception)
{
	$data = "<pre>" . htmlspecialchars(print_r($exception, true)) . "</pre>";
}
catch(EvaluatorException $exception)
{
	$data = "<pre>" . htmlspecialchars(print_r($exception, true)) . "</pre>";
}
catch(\Exception $exception)
{
	$data = "<pre>" . htmlspecialchars(print_r($exception, true)) . "</pre>";
}

echo $data;

die();
```

Top5Games.tpl (NOTE: `format_seconds` is a conceptual modifier and will throw an `EvaluatorException`- it is used here just to show how the library could be extended to support such functionality in the future)
```
<h1>Hello {{user.names.first}} {{user.names.surname|uppercase}}</h1>

{% if user.grade == user_grades.ADMIN %}
	<form method="GET">
		<label for="games_username">Username</label>
		<input type="text" id="games_username" name="username" value="" />

		<input type="submit" value="Search" />
	</form>
{% endif %}

<p>Here is a summary of your top <b>5</b> games:</p>

{% if games|count > 0 %}
	<ul>
		{% foreach id, game in games %}
			<li>
				<h2>Game #{{game.id}}</h2>

				{% game.is_time_based %}
					<p>&bullet; Total Time Spent in Objective: {{game.time_in_objective|format_seconds(i:s)}}</p>
				{% else if game.is_score_based %}
					<p>&bullet; Total Score: {{game.score}}</p>
				{% else %}
					<p>&bullet; Total Kills: {{game.kills}}</p>

					<h3>Players Killed:</h3>

					<ul>
						{% foreach kill in game.kills %}
							<li><p>&bullet; Name: {{kill.player.name|uppercase}}</p></li>
						{% endforeach %}
					</ul>
				{% endif %}
			</li>
		{% endforeach %}
	</ul>
{% endif %}
```
