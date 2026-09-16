"""Download pinned, official UI distributions for offline presentation."""
from pathlib import Path
import urllib.request
import tarfile
import io

root = Path(__file__).resolve().parents[1] / 'public/assets/vendor'
root.mkdir(parents=True, exist_ok=True)
packages = {
    'bootstrap': ('5.3.8', {'dist/css/bootstrap.min.css': 'bootstrap.min.css', 'dist/js/bootstrap.bundle.min.js': 'bootstrap.bundle.min.js'}),
    'jquery': ('3.7.1', {'dist/jquery.min.js': 'jquery.min.js'}),
    'datatables.net': ('2.3.4', {'js/dataTables.min.js': 'dataTables.min.js'}),
    'datatables.net-bs5': ('2.3.4', {'js/dataTables.bootstrap5.min.js': 'dataTables.bootstrap5.min.js', 'css/dataTables.bootstrap5.min.css': 'dataTables.bootstrap5.min.css'}),
    'chart.js': ('4.5.1', {'dist/chart.umd.min.js': 'chart.umd.min.js'}),
    'sweetalert2': ('11.26.3', {'dist/sweetalert2.all.min.js': 'sweetalert2.all.min.js'}),
}
for package, (version, files) in packages.items():
    url = f'https://registry.npmjs.org/{package}/-/{package}-{version}.tgz'
    with urllib.request.urlopen(url, timeout=30) as response:
        archive = tarfile.open(fileobj=io.BytesIO(response.read()), mode='r:gz')
    for path, name in files.items():
        (root / name).write_bytes(archive.extractfile('package/' + path).read())
    for member in archive.getmembers():
        if member.name.lower() in ['package/license', 'package/license.txt', 'package/license.md']:
            (root / (package + '-LICENSE.txt')).write_bytes(archive.extractfile(member).read())
    print(package, version)
