# Villain
A small PHP library used to help build any form of application.

# How to use
Simply extract Villain to any directory, and include the root file Villain.php.
Villain.php will autoload any Villain classes you chose to use automatically.
**Make sure to `use` the correct namespaces**

# Templating
Templating currently supports 3 types of data:
## Plain Data
Data which will simply be output as is and start with the tag
## Variable statements
Statements which will be evaluated, modified (if a modifier is supplied) and the value output in place
## Expression statements
Statements which include things such as if and foreach.

## Starting/Ending Tags
Starting and Ending tags surround the type of data it represents. These can be changed by passing respective values to the Lexer::Lex `options` parameter.

Defaults:
Plain Data - NONE
Variables - `{{` and `}}`
Expressions - `{%` and `%}`

## Templating Example
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
					<p>&bullet; Total Time Spent in Objective: {{game.time_in_objective}}</p>
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
{% else %}
```
