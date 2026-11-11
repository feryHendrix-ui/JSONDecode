import {readFileSync} from 'node:fs';
import {spawn} from 'node:child_process';
const envFile = (()=>{try{return readFileSync('.env','utf8');}catch{return ''}})();
const localFranken = envFile.match(/^FRANKENPHP_BINARY=(.+)$/m)?.[1]?.replace(/^["']|["']$/g,'');
const franken = process.env.FRANKENPHP_BINARY || localFranken;
const portIndex = process.argv.indexOf('--port');
const hostIndex = process.argv.indexOf('--host');
const port = portIndex >= 0 ? process.argv[portIndex + 1] : (process.env.PORT || '8000');
const host = hostIndex >= 0 ? process.argv[hostIndex + 1] : '0.0.0.0';
const args = franken ? ['php-server','--listen',host+':'+port,'--root','public']
    : ['-S',host+':'+port,'-t','public','tools/router.php'];
const child=spawn(franken || 'php',args,{stdio:'inherit',env:process.env});
child.on('error',()=>{console.error('PHP belum tersedia. Aktifkan PHP pada terminal Laragon atau jalankan php artisan serve.');process.exitCode=1;});
for(const sig of ['SIGTERM','SIGINT']) process.on(sig,()=>child.kill(sig));
child.on('exit',code=>process.exit(code || 0));
