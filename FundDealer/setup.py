#!/usr/bin/python
# -*- coding: iso-8859-1 -*-

from distutils.core import setup
from glob import glob
import py2exe

data_files = [("Microsoft.VC90.CRT", glob(r'C:\Coltrane\Projects\Petra\FundDealer\dlls\Microsoft.VC90.CRT\*.*'))]

setup(
        options = {
            "py2exe": {
                "dll_excludes":["MSVCP90.dll"]
            }
        },
        data_files = data_files,
        console = ['processor.py']
)

