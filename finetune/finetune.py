from openai import OpenAI
client = OpenAI()

client.files.create(
  file=open("mydata.json", "rb"),
  purpose="fine-tune"
)