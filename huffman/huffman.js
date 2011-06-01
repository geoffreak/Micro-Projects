// JavaScript Document

function PriorityQueue()
{
	this.priorityArray = [];
	this.insert = function (name, priority) {
		var i = 0;
		while (i <= this.priorityArray.length && priority > ((this.priorityArray[i] || {"priority": Infinity}).priority || Infinity)) {                            
			i++;
		}                   
		this.priorityArray.splice(i, 0, {"name": name, "priority": priority});
		return true;            
	}
	this.get = function () {
		return (this.priorityArray.shift() || {"name": undefined}).name;
	}
	this.peek = function () {
		return (this.priorityArray[0] || {"name": undefined}).name;
	}
	this.length = function() {
		return this.priorityArray.length;
	}
}
function Node(val, frequency)
{
	this.val = val;
	this.freq = frequency;
	this.left, this.right, this.code = '';
}

var frequencies = {
	'a': 0.60,
	'b': 0.05,
	'c': 0.30,
	'd': 0.05
};
console.log('Frequency table:');
console.log(frequencies);

var pq = new PriorityQueue();

//load up priority queue
for (letter in frequencies)
{
	var newNode = new Node(letter, frequencies[letter]);
	pq.insert(newNode, newNode.freq);
}

//turn queue into tree
while (pq.length() > 1)
{
	var node1 = pq.get(), node2 = pq.get();
	var newNode = new Node(false, node1.freq + node2.freq);
	newNode.left = node1, newNode.right = node2;
	pq.insert(newNode, newNode.freq);
}

var allNodes = new Array();
var root = pq.get();
allNodes.push(root);

var codes = {};

//process codes for each letter
while (allNodes.length > 0)
{
	var nextNode = allNodes.shift();
	if (nextNode.val) //is a letter
	{
		codes[nextNode.val] = nextNode.code;
	}
	else //is a holder node
	{
		nextNode.left.code = nextNode.code + '1';
		nextNode.right.code = nextNode.code + '0';
		allNodes.push(nextNode.left);
		allNodes.push(nextNode.right);
	}
}

console.log('Tree output:');
console.log(codes);

//encode the string
var string = 'abacdaacac';
var newString = '';
for (var i = 0; i < string.length; i++)
{
	newString += codes[string.charAt(i)];
}

console.log('Encoding "'+string+'" ==> "' +newString + '"');
//decode time
var decode = '011011110';
var decoded = '';
for (var i = 0; i < decode.length;)
{
	var nextNode = root;
	while (!nextNode.val) // follow the string until we find a character
	{
		if (decode.charAt(i) == '0')
		{
			nextNode = nextNode.right
		}
		else if (decode.charAt(i) == '1')
		{
			nextNode = nextNode.left
		}
		else
		{
			console.log('bad character');
		}
		i++;
	}
	if (!nextNode.val) console.log('could not find node');
	decoded += nextNode.val;
}

console.log('Decoding "'+decode+'" ==> "' +decoded + '"');