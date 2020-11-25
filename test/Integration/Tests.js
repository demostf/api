/**
 * parser server
 */
var DemoParser = require('tf2-demo');
var express = require('express');
var app = express();
var url = require('url');
var https = require('https');
var http = require('http');

app.set('port', (process.env.PORT || 80));
app.use(express.static(__dirname + '/public'));

app.get('/', function (request, response) {
	response.send('Hello World!');
});

function handleDataStream(stream, cb) {
	var buffers = [];
	stream.on('data', function (buffer) {
		buffers.push(buffer);
	});
	stream.on('end', function () {
		try {
			var buffer = Buffer.concat(buffers);
			var demo = DemoParser.Demo.fromNodeBuffer(buffer);
			var parser = demo.getParser(true);
			var header = parser.readHeader();
			var match = parser.parseBody();
			var body = match.getState();
			body.header = header;
			cb(body);
		} catch (e) {
			cb(e);
		}
	});
}

app.post('/parse', function (req, res) {
	handleDataStream(req, function (body) {
		res.set('Content-Type', 'application/json');
		res.write(JSON.stringify(body));
		res.end();
	})
});

app.listen(9123);


const chakram = require('chakram');
const expect = chakram.expect;
const root = 'http://localhost:8000/';
const fs = require('fs');

process.env.PARSER_URL = `http://localhost:9123/parse`;
process.env.EDIT_SECRET = 'edit_key';

chakram.setRequestDefaults({baseUrl: root});

before((done) => {
	console.log('spawn server');
	const server = require('child_process').spawn('php', ['-S', '0.0.0.0:8000', 'router.php'], {
		cwd: __dirname + '/../',
		env: process.env
	});
	server.stdout.on('data', (data) => {
		console.log(`stdout: ${data}`);
	});
	server.stderr.on('data', (data) => {
		console.log(`stderr: ${data}`);
	});
	after(() => {
		console.log('clean server');
		server && server.kill();
	});
	setTimeout(done, 1000);
});

before("reset db", function () {
	chakram.post("reset");
	return chakram.wait();
});

beforeEach("create test user", function () {
	chakram.post("reset");
	return chakram.wait().then(function() {
		chakram.post("testuser");
		return chakram.wait();
	});
});

afterEach("reset db", function () {
	chakram.post("reset");
	return chakram.wait();
});

function uploadDemo(file) {
	return chakram.post("upload", undefined, {
		formData: {
			name: 'foo',
			blue: 'BLU',
			red: 'RED',
			demo: fs.createReadStream(file),
			key: 'key1'
		}
	}).then((response) => {
		console.log(`body: "${response.body}"`);
		return parseInt(response.body.match(/\/(\d+)/)[1], 10);
	});
}

chakram.addMethod("text", function (respObj, text) {
	const body = respObj.response.body;
	this.assert(body === text,
		'expected response text ' + body + ' to equal ' + text,
		'expected response text ' + body + ' to not be equal to ' + text);
});
chakram.addMethod("containsText", function (respObj, text) {
	const body = respObj.response.body;
	this.assert(body.indexOf(text) !== -1,
		'expected response text ' + body + ' to contain ' + text,
		'expected response text ' + body + ' to not contain ' + text);
});
describe("Upload", function () {
	this.timeout(1000 * 30);
	it("fails without valid key", function () {
		const response = chakram.post("upload", undefined, {
			formData: {
				name: 'foo',
				blue: 'BLU',
				red: 'RED',
				demo: fs.createReadStream(__dirname + '/../data/product.dem'),
				key: 'dummy'
			}
		});
		expect(response).to.have.status(401);
		expect(response).to.be.text('Invalid key');
		return chakram.wait();
	});

	it("returns the demo path on success", function () {
		const response = chakram.post("upload", undefined, {
			formData: {
				name: 'foo',
				blue: 'BLU',
				red: 'RED',
				demo: fs.createReadStream(__dirname + '/../data/product.dem'),
				key: 'key1'
			}
		});
		// expect(response).to.have.status(401);
		expect(response).to.be.containsText('STV available at: ');
		return chakram.wait();
	});
});

describe("Demo listing", function () {
	this.timeout(1000 * 30);

	it("starts empty", function () {
		const response = chakram.get("demos");
		expect(response).to.have.status(200);
		expect(response).to.have.header("content-type", "application/json; charset=utf-8");
		expect(response).to.comprise.of.json([]);
		return chakram.wait();
	});

	it("contains uploaded demo", function () {
		return uploadDemo(__dirname + '/../data/product.dem').then(id => {
			return chakram.get("demos").then(response => {
				const body = response.body;
				expect(body[0].id).to.be.equal(id);
				expect(body[0].name).to.be.equal('foo');
				expect(body[0].server).to.be.equal('UGC Highlander Match');
				expect(body[0].duration).to.be.equal(778);
				expect(body[0].nick).to.be.equal('SourceTV Demo');
				expect(body[0].map).to.be.equal('koth_product_rc8');
				expect(body[0].red).to.be.equal('RED');
				expect(body[0].blue).to.be.equal('BLU');
				expect(body[0].redScore).to.be.equal(3);
				expect(body[0].blueScore).to.be.equal(0);
				expect(body[0].playerCount).to.be.equal(18);
			});
		});
	});
});

describe("Set url", function () {
	this.timeout(1000 * 30);

	it("fails with invalid key", function () {
		return uploadDemo(__dirname + '/../data/product.dem').then(id => {
			return chakram.get("demos").then(response => {
				const setUrl = chakram.post(`/demos/${id}/url`, undefined, {
					formData: {
						hash: 'asd',
						backend: 'foo',
						url: 'http://bar',
						path: 'bar',
						key: 'foo'
					}
				});
				expect(setUrl).to.be.containsText('Invalid key');
				expect(setUrl).to.have.status(401);
				return chakram.wait();
			});
		});
	});

	it("fails with invalid hash", function () {
		return uploadDemo(__dirname + '/../data/product.dem').then(id => {
			return chakram.get("demos").then(response => {
				const setUrl = chakram.post(`/demos/${id}/url`, undefined, {
					formData: {
						hash: 'asd',
						backend: 'foo',
						url: 'http://bar',
						path: 'bar',
						key: 'edit_key'
					}
				});
				expect(setUrl).to.be.containsText('Invalid demo hash');
				expect(setUrl).to.have.status(412);
				return chakram.wait();
			});
		});
	});

	it("changes url, backend and path on success", function () {
		return uploadDemo(__dirname + '/../data/product.dem').then(id => {
			return chakram.get(`demos/${id}`).then(response => {
				const hash = response.body.hash;

				const setUrl = chakram.post(`/demos/${id}/url`, undefined, {
					formData: {
						hash: hash,
						backend: 'foo',
						url: 'http://bar',
						path: 'bar',
						key: 'edit_key'
					}
				});
				expect(setUrl).to.have.status(200);
				return setUrl.then(response => {
					return chakram.get(`demos/${id}`)
				}).then(response => {
					const body = response.body;
					expect(body.id).to.be.equal(id);
					expect(body.backend).to.be.equal('foo');
					expect(body.url).to.be.equal('http://bar');
					expect(body.path).to.be.equal('bar');
				});
			});
		});
	});
});
