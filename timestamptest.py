from datetime import datetime
from zoneinfo import ZoneInfo


def get_local_timestamp():
    """Aktuellen Zeitstempel für Europe/Berlin liefern."""
    return datetime.now(ZoneInfo("Europe/Berlin")).strftime("%Y-%m-%d %H:%M:%S")


if __name__ == "__main__":
    print(get_local_timestamp())
