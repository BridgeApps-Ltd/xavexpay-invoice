
# to go in Gati.py
class Gati:
    def __init__(self):
        self._name="Gati"
        

    def get_tracking_info(self, lr):
        self.lr= lr
        return "Information after calling Gati - Tracking API "


# to go in Vxpress.py
class Vxpress:
    def __init__(self):
        self._name="Vxpess"

    def get_tracking_info(self, lr):
        self.lr= lr
        return "Information after calling Gati - Tracking API "

# to go in LspFactory.py
class LspFactory:
    def __init__(self):
        self._transporters = {
            # Modify this list below as and when a new transporter is onboarded.
            "GATI": Gati(),
            "VXPRESS": Vxpress()
        }

    def get_lsp(self, lsp_name):
        LspClass = self._transporters[lsp_name.upper()]
        if LspClass is None:
            raise ValueError(f"Invalid LSP type: {lsp_name}")
        return LspClass


# ---------- Code to go in common.py or anywhere else where this is being called ---------------
# instantiate this at the global level at the start of app.py
factory = LspFactory()

# call this whenever you need, change the name of the lsp
lsp_gati = factory.get_lsp("gati")

# just use the same object to call different APIs as needed
print(lsp_gati.get_tracking_info('121')) 
